<?php

use Livewire\Mechanisms\ComponentRegistry;
use MountainClans\LivewireSectionBuilder\Livewire\AdminSectionBuilder;
use MountainClans\LivewireSectionBuilder\Livewire\FrontendSectionViewer;
use MountainClans\LivewireSectionBuilder\LivewireSectionBuilderServiceProvider;

it('boots the service provider', function () {
    expect(app()->getLoadedProviders())
        ->toHaveKey(LivewireSectionBuilderServiceProvider::class);
});

it('registers the livewire components', function () {
    $registry = app(ComponentRegistry::class);

    expect($registry->getClass('admin-section-builder'))->toBe(AdminSectionBuilder::class)
        ->and($registry->getClass('frontend-section-viewer'))->toBe(FrontendSectionViewer::class);
});

it('registers the repeater-editor blade component alias', function () {
    $aliases = app('blade.compiler')->getClassComponentAliases();

    expect($aliases)->toHaveKey('admin.repeater-editor');
});
