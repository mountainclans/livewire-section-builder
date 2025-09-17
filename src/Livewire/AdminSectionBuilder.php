<?php

namespace MountainClans\LivewireSectionBuilder\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use MountainClans\LivewireSectionBuilder\Models\BuilderSection;

class AdminSectionBuilder extends Component
{
    public const EVENT_SECTION_ADDED = 'section_builder_section_added';
    public const EVENT_SECTION_UPDATED = 'section_builder_section_updated';
    public const EVENT_SECTION_SORTED = 'section_builder_section_sorted';
    public const EVENT_SECTION_HIDDEN = 'section_builder_section_hidden';
    public const EVENT_SECTION_SHOWED = 'section_builder_section_showed';
    public const EVENT_SECTION_DELETED = 'section_builder_section_deleted';

    public string $template;
    public string $pageId;
    public string $newSectionType = '';

    public array $availableSections = [];
    public array $sectionModels = [];

    public function mount(string $template, string $pageId): void
    {
        $this->template = $template;
        $this->pageId = $pageId;

        $this->setAvailableSections();
        $this->setSectionModels();
    }

    private function setAvailableSections(): void
    {
        $templates = config('livewire-section-builder.templates');
        $registeredSections = config('livewire-section-builder.sections');
        $templateSections = array_key_exists($this->template, $templates) ? $templates[$this->template] : null;

        if (empty($templateSections)) {
            $this->availableSections = [];
            return;
        }

        $availableSections = [];
        foreach ($registeredSections as $registeredSection) {
            if (in_array($registeredSection['key'], $templateSections)) {
                $availableSections[$registeredSection['key']] = $registeredSection['title'];
            }
        }

        $this->availableSections = $availableSections;
    }

    private function setSectionModels(): void
    {
        $this->sectionModels = BuilderSection::query()
            ->where([
                'template' => $this->template,
                'page_id' => $this->pageId,
            ])
            ->orderBy('order_column')
            ->get()
            ->all();
    }

    public function addSection(): void
    {
        $lastOrder = BuilderSection::query()
            ->where([
                'template' => $this->template,
                'page_id' => $this->pageId,
            ])
            ->max('order_column');

        BuilderSection::create([
            'type' => $this->newSectionType,
            'template' => $this->template,
            'page_id' => $this->pageId,
            'order_column' => ++$lastOrder,
        ]);

        $this->setSectionModels();
        $this->dispatch(self::EVENT_SECTION_ADDED);
    }

    #[On(self::EVENT_SECTION_UPDATED)]
    public function onSectionUpdate(): void
    {
        $this->setSectionModels();
    }

    public function toggleSectionVisibility(string $sectionId): void
    {
        $section = BuilderSection::query()->withSubclasses()->findOrFail($sectionId);

        $section->is_visible = !$section->is_visible;

        $section->save();

        $this->setSectionModels();

        if ($section->is_visible) {
            $this->dispatch(self::EVENT_SECTION_SHOWED);
        } else {
            $this->dispatch(self::EVENT_SECTION_HIDDEN);
        }
    }

    public function sortSections(string $sectionId, int $position): void
    {
        $currentOrder = array_column($this->sectionModels, 'id');

        $currentIndex = array_search($sectionId, $currentOrder);
        if ($currentIndex === false) {
            return;
        }

        unset($currentOrder[$currentIndex]);

        $currentOrder = array_values($currentOrder);
        array_splice($currentOrder, $position, 0, $sectionId);

        BuilderSection::setNewOrder($currentOrder);

        $this->setSectionModels();
        $this->dispatch(self::EVENT_SECTION_SORTED);
    }

    public function deleteSection(string $sectionId): void
    {
        $section = BuilderSection::query()->withSubclasses()->findOrFail($sectionId);

        $section->delete();

        $this->setSectionModels();
        $this->dispatch(self::EVENT_SECTION_DELETED);
    }

    public function render(): View
    {
        return view('livewire-section-builder::livewire.admin-section-builder');
    }
}
