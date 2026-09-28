<?php

use Livewire\Livewire;
use MountainClans\LivewireSectionBuilder\Livewire\AdminSectionBuilder;
use MountainClans\LivewireSectionBuilder\Livewire\FrontendSectionViewer;
use MountainClans\LivewireSectionBuilder\LivewireSectionBuilderServiceProvider;

it('boots the service provider', function () {
    expect(app()->getLoadedProviders())
        ->toHaveKey(LivewireSectionBuilderServiceProvider::class);
});

it('registers the livewire components', function () {
    // Публичный API: внутренний реестр компонентов в Livewire 3 и 4 называется по-разному
    expect(Livewire::new('admin-section-builder'))->toBeInstanceOf(AdminSectionBuilder::class)
        ->and(Livewire::new('frontend-section-viewer'))->toBeInstanceOf(FrontendSectionViewer::class);
});

it('registers the repeater-editor blade component alias', function () {
    $aliases = app('blade.compiler')->getClassComponentAliases();

    expect($aliases)->toHaveKey('admin.repeater-editor');
});
