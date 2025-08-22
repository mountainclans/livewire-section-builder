<?php

namespace MountainClans\LivewireSectionBuilder;

use Livewire\Livewire;
use MountainClans\LivewireSectionBuilder\Livewire\AdminSectionBuilder;
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
            ->hasMigration('create_livewire_section_builder_table');
    }

    public function packageBooted(): void
    {
        Livewire::component('admin-section-builder', AdminSectionBuilder::class);
    }
}
