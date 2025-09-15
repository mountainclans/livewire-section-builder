<?php

namespace MountainClans\LivewireSectionBuilder\Livewire;

use Illuminate\View\View;
use Livewire\Component;
use MountainClans\LivewireSectionBuilder\Models\BuilderSection;

class FrontendSectionViewer extends Component
{
    public string $template;
    public string $pageId;

    public array $sectionModels = [];

    public function mount(string $template, string $pageId): void
    {
        $this->template = $template;
        $this->pageId = $pageId;

        $this->setSectionModels();
    }

    private function setSectionModels(): void
    {
        $templates = config('livewire-section-builder.templates');
        $availableSectionTemplates = array_key_exists($this->template, $templates) ? $templates[$this->template] : [];

        $this->sectionModels = BuilderSection::query()
            ->where([
                'template' => $this->template,
                'page_id' => $this->pageId,
            ])
            ->whereIn('type', $availableSectionTemplates)
            ->orderBy('order_column')
            ->get()
            ->all();
    }

    public function render(): View
    {
        return view('livewire-section-builder::livewire.frontend-section-viewer');
    }
}
