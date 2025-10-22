<?php

namespace MountainClans\LivewireSectionBuilder;

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use MountainClans\LivewireSectionBuilder\Livewire\AdminSectionBuilder;
use MountainClans\LivewireSectionBuilder\Livewire\FrontendSectionViewer;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LivewireSectionBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('livewire-section-builder')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigration('create_livewire_section_builder_table')
            ->hasMigration('create_livewire_section_builder_repeaters_table');
    }

    public function packageBooted(): void
    {
        Livewire::component('admin-section-builder', AdminSectionBuilder::class);
        Livewire::component('frontend-section-viewer', FrontendSectionViewer::class);

        Blade::component('livewire-section-builder::components/repeater-editor', 'admin.repeater-editor');
    }
}
