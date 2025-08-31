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

Перед использованием компонентов пакета, пожалуйста, сконфигурируйте используемые классы в конфигурационном файле.

### Модель секции
Модель секции должна расширять класс

Структура хранения модели состоит из следующих полей:
- id
- template
- page_id
- type
- order_column
- fields
- created_at
- updated_at

Не рекомендуется менять миграцию, создающую таблицу для секций. Все необходимые поля секции можно сохранить в атрибуте модели `fields`. Данный атрибут представляет собой [schemaless attribute, реализация от Spatie](https://github.com/spatie/laravel-schemaless-attributes).

Тем не менее, Вы вправе расширять модель секции как Вам необходимо, к примеру, использовать трейты `HasTranslations`, `InteractsWithMedia` и другие.

### Админка
В нужном месте страницы используйте компонент редактора:

```bladehtml
@if ($pageId && !empty($destination))
    <livewire:admin-section-builder :template="$destination" 
                                    :page-id="$pageId"
    />
@endif
```

Компоненты `editor` сами занимаются валидацией и сохранением своего состояния, в них может быть любая логика. Удалением, сортировкой, скрытием и показом контента этих компонентов занимается компонент `admin-section-builder`. 

Настоятельно рекомендуется, чтобы компонент взаимодействовал только с соответствующей моделью, указанной в конфиге как `model`.

Рекомендуется создавать как минимум один публичный метод - `saveSection`, но в целом реализация никак не регламентирована.

При сохранении компонента рекомендуется выбрасывать событие:

```php
$this->dispatch(AdminSectionBuilder::EVENT_SECTION_UPDATED);
```

#### События при редактировании в админке

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
