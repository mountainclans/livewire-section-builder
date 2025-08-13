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
