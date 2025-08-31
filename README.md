# Livewire Section Builder

## Установка

Установите пакет при помощи composer:

```bash
composer require mountainclans/livewire-section-builder
```

**Важно!** Пакет зависит от других компонентов, в частности, необходимо сразу после установки этого пакета также сконфигурировать для использования пакет [mountainclans/livewire-select](https://github.com/mountainclans/livewire-select) (инструкции находятся на его странице).

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
    'templates' => [
        `<template_alias>` => [
            `section_1_alias` => [
                'key' => 'top_banner',
                'title' => 'Top banner',
                'model' => EloquentSectionModelClass::class,
                'editor' => LivewireEditorComponent::class,
                'frontend' => LivewireViewComponent::class,
            ],
            `section_1_alias` => [...]
        ],
        `<another_template_alias` => [...], 
    ],
]
```

---

Добавьте в tailwind.config.js в секцию content:

```js
'./vendor/mountainclans/livewire-section-builder/resources/views/**/*.blade.php'
```

Опционально, Вы можете опубликовать `views` для их переопределения:

```bash
php artisan vendor:publish --tag="livewire-section-builder-views"
```

## Использование

### События

Вы можете расширить класс компонента `AdminSectionBuilder` и использовать собственный компонент в админке. В частности, Вы можете подписаться на одно из следующих событий:

-  AdminSectionBuilder::EVENT_SECTION_ADDED
-  AdminSectionBuilder::EVENT_SECTION_UPDATED
-  AdminSectionBuilder::EVENT_SECTION_SORTED
-  AdminSectionBuilder::EVENT_SECTION_HIDDEN
-  AdminSectionBuilder::EVENT_SECTION_SHOWED
-  AdminSectionBuilder::EVENT_SECTION_DELETED

И реализовать, к примеру, отправку уведомлений пользователю.

## Авторы

- [Vladimir Bajenov](https://github.com/mountainclans)
- [All Contributors](../../contributors)

## Лицензия

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
