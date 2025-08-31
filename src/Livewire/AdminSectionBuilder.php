<?php

namespace MountainClans\LivewireSectionBuilder\Livewire;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use MountainClans\LivewireSectionBuilder\Exceptions\InvalidSectionTemplate;
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
    public Collection $sectionModels;

    /**
     * @throws InvalidSectionTemplate
     */
    public function mount(string $template, string $pageId): void
    {
        $this->template = $template;
        $this->pageId = $pageId;

        $this->setAvailableSections();
        $this->setSectionModels();
    }

    /**
     * @throws InvalidSectionTemplate
     */
    private function setAvailableSections(): void
    {
        $templates = config('livewire-section-builder.templates');

        if (array_key_exists($this->template, $templates)) {
            $sections = [];
            foreach ($templates[$this->template] as $sectionTemplate) {
                $sections[$sectionTemplate['key']] = $sectionTemplate['title'];
            }
            $this->availableSections = $sections;
        } else {
            throw new InvalidSectionTemplate('Please select the correct template or fill the config file.');
        }
    }

    private function setSectionModels(): void
    {
        $this->sectionModels = BuilderSection::query()
            ->where([
                'template' => $this->template,
                'page_id' => $this->pageId,
            ])
            ->orderBy('order_column')
            ->get();
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
            'type' =>  "{$this->template}_{$this->newSectionType}",
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

    public function sortSections(string $sectionId, int $position): void
    {
        $currentOrder = $this->sectionModels
            ->pluck('id')
            ->toArray();

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
