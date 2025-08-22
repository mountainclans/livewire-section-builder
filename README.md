# Livewire Section Builder

## Установка

Установите пакет при помощи composer:

```bash
composer require mountainclans/livewire-section-builder
```

Опубликуйте и примените миграции:

```bash
php artisan vendor:publish --tag="livewire-section-builder-migrations"
php artisan migrate
```

Опубликуйте конфигурационный файл:

```bash
php artisan vendor:publish --tag="livewire-section-builder-config"
```

Конфигурационный массив имеет следующую вложенность:
```php
retyrn [
    `<template_alias>` => [
        `section_1_alias` => [
            'key' => 'top_banner',
            'title' => 'Top banner',
            'model' => EloquentSectionModelClass::class,
            'editor' => LivewireEditorComponent::class,
            'view' => LivewireViewComponent::class,
        ],
        `section_1_alias` => [...]
    ],
    `<another_template_alias` => [...],   
]
```

---

Опционально, Вы можете опубликовать `views` для их переопределения:

```bash
php artisan vendor:publish --tag="livewire-section-builder-views"
```

## Использование

## Авторы

- [Vladimir Bajenov](https://github.com/mountainclans)
- [All Contributors](../../contributors)

## Лицензия

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
