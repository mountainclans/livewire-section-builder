<?php

use MountainClans\LivewireSectionBuilder\Support\RegistryValidator;
use MountainClans\LivewireSectionBuilder\Tests\Fixtures\ApiSection;

beforeEach(function () {
    // Реестр с headless-секцией (без 'frontend').
    config()->set('livewire-section-builder.sections', [
        [
            'key' => 'api_section',
            'title' => 'API section',
            'model' => ApiSection::class,
            'editor' => 'stub-editor',
        ],
    ]);

    config()->set('livewire-section-builder.templates', [
        'test_page' => ['api_section'],
    ]);
});

it('валидация выключена по умолчанию — не запускается даже на невалидном реестре', function () {
    config()->set('livewire-section-builder.sections', [['key' => 'broken']]);

    (new RegistryValidator)();
})->throwsNoExceptions();

it('в Livewire-режиме секция без frontend не проходит валидацию', function () {
    config()->set('livewire-section-builder.validate_registry', true);

    (new RegistryValidator)();
})->throws(InvalidArgumentException::class, "missing required config key 'frontend'");

it('в headless-режиме секция без frontend валидна', function () {
    config()->set('livewire-section-builder.validate_registry', true);
    config()->set('livewire-section-builder.headless', true);

    (new RegistryValidator)();
})->throwsNoExceptions();

it('шаблон со ссылкой на незарегистрированную секцию не проходит валидацию', function () {
    config()->set('livewire-section-builder.validate_registry', true);
    config()->set('livewire-section-builder.headless', true);
    config()->set('livewire-section-builder.templates', [
        'test_page' => ['api_section', 'unknown_section'],
    ]);

    (new RegistryValidator)();
})->throws(InvalidArgumentException::class, "references unknown section 'unknown_section'");
