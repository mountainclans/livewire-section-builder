<?php

namespace MountainClans\LivewireSectionBuilder\Livewire;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use MountainClans\LivewireSectionBuilder\Exceptions\InvalidSectionTemplate;
use MountainClans\LivewireSectionBuilder\Models\BuilderSection;

class AdminSectionBuilder extends Component
{
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
        $newSection = BuilderSection::create([
            'type' =>  "{$this->template}_{$this->newSectionType}",
            'template' => $this->template,
            'page_id' => $this->pageId,
        ]);
        $newSection = $newSection->refresh();

        $this->sectionModels->add($newSection);
    }

    public function sortSections()
    {
        // todo
    }

    public function render(): View
    {
        return view('livewire-section-builder::livewire.admin-section-builder');
    }
}
