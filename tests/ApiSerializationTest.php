<?php

use MountainClans\LivewireSectionBuilder\Tests\Fixtures\ApiSection;
use MountainClans\LivewireSectionBuilder\Tests\Fixtures\ApiSectionRepeater;

beforeEach(function () {
    (include __DIR__.'/../database/migrations/create_livewire_section_builder_table.php.stub')->up();
    (include __DIR__.'/../database/migrations/create_livewire_section_builder_repeaters_table.php.stub')->up();

    config()->set('livewire-section-builder.sections', [
        [
            'key' => 'api_section',
            'title' => 'API section',
            'model' => ApiSection::class,
            'editor' => 'stub-editor',
            'schema' => 'stub-schema',
            // 'frontend' отсутствует — headless-секция без Livewire-представления.
        ],
    ]);

    config()->set('livewire-section-builder.templates', [
        'test_page' => ['api_section'],
    ]);

    config()->set('app.fallback_locale', 'en');

    // Не глобальная функция-хелпер: Pest выполняет все тест-файлы
    // в одном процессе, глобальные функции конфликтуют по именам.
    $section = new ApiSection([
        'template' => 'test_page',
        'page_id' => 'main',
    ]);

    $section->fields->set('title', ['en' => 'Hello', 'ru' => 'Привет']);
    $section->fields->set('button', 'Click me');
    $section->save();

    $this->section = $section;
});

it('toApiArray резолвит переводимые поля по локали, обычные отдаёт как есть', function () {
    $api = $this->section->toApiArray('ru');

    expect($api['title'])->toBe('Привет')
        ->and($api['button'])->toBe('Click me')
        ->and($api['repeaters'])->toBe([]);
});

it('toApiArray: отсутствующий перевод — фолбэк на app.fallback_locale', function () {
    expect($this->section->toApiArray('es')['title'])->toBe('Hello');
});

it('toApiArray отдаёт репитеры по order_column с id и переводами', function () {
    $section = $this->section;

    $second = new ApiSectionRepeater(['section_id' => $section->id, 'order_column' => 2]);
    $second->fields->set('label', ['en' => 'Second', 'ru' => 'Второй']);
    $second->fields->set('icon', 'phone');
    $second->save();

    $first = new ApiSectionRepeater(['section_id' => $section->id, 'order_column' => 1]);
    $first->fields->set('label', ['en' => 'First', 'ru' => 'Первый']);
    $first->fields->set('icon', 'advice');
    $first->save();

    $repeaters = $section->toApiArray('ru')['repeaters'];

    expect($repeaters)->toHaveCount(2)
        ->and($repeaters[0]['id'])->toBe($first->id)
        ->and($repeaters[0]['label'])->toBe('Первый')
        ->and($repeaters[0]['icon'])->toBe('advice')
        ->and($repeaters[1]['id'])->toBe($second->id)
        ->and($repeaters[1]['label'])->toBe('Второй');
});

it('frontendComponent — null для headless-секции, editorComponent работает', function () {
    expect($this->section->frontendComponent())->toBeNull()
        ->and($this->section->editorComponent())->toBe('stub-editor');
});

it('apiSchema резолвит класс схемы из реестра', function () {
    expect($this->section->apiSchema())->toBe('stub-schema');
});
