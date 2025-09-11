<?php

namespace MountainClans\LivewireSectionBuilder\Livewire;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use MountainClans\LivewireSectionBuilder\Models\BuilderSection;

class FrontendSectionViewer extends Component
{
    public string $template;
    public string $pageId;

    public Collection $sectionModels;

    public function mount(string $template, string $pageId): void
    {
        $this->template = $template;
        $this->pageId = $pageId;

        $this->setSectionModels();
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

    public function render(): View
    {
        return view('livewire-section-builder::livewire.frontend-section-viewer');
    }
}
