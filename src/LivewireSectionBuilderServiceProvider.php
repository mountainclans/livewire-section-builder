<?php

namespace MountainClans\LivewireSectionBuilder;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MountainClans\LivewireSectionBuilder\Commands\LivewireSectionBuilderCommand;

class LivewireSectionBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('livewire-section-builder')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_livewire_section_builder_table')
            ->hasCommand(LivewireSectionBuilderCommand::class);
    }
}
