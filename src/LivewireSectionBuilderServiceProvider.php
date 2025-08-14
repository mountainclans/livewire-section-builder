<?php

namespace MountainClans\LivewireSectionBuilder;

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
            ->hasMigration('create_livewire_section_builder_table');
    }
}
