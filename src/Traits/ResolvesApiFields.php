<?php

namespace MountainClans\LivewireSectionBuilder\Traits;

use MountainClans\LivewireSectionBuilder\Contracts\SerializesMedia;
use MountainClans\LivewireSectionBuilder\Support\DefaultMediaSerializer;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\SchemalessAttributes\SchemalessAttributes;

/**
 * Сериализация schemaless-полей модели в API-массив (headless-режим).
 * Переводимые поля (ключи вида 'fields->title' в $translatable) резолвятся
 * в запрошенную локаль с фолбэком на app.fallback_locale.
 *
 * @property SchemalessAttributes $fields
 * @property array $translatable объявляется моделями приложения (spatie HasTranslations)
 */
trait ResolvesApiFields
{
    protected function resolveApiFields(string $locale): array
    {
        $translatable = property_exists($this, 'translatable') ? $this->translatable : [];

        $result = [];

        foreach ($this->fields->all() as $key => $value) {
            $result[$key] = in_array("fields->{$key}", $translatable, true) && is_array($value)
                ? $this->resolveTranslatableValue($value, $locale)
                : $value;
        }

        return $result;
    }

    protected function resolveTranslatableValue(array $translations, string $locale): mixed
    {
        if (array_key_exists($locale, $translations)) {
            return $translations[$locale];
        }

        $fallback = (string) config('app.fallback_locale', '');

        return $fallback !== '' ? ($translations[$fallback] ?? '') : '';
    }

    /**
     * Медиа-коллекция модели → массив для API через настроенный сериализатор
     * (config 'media_serializer'); формат объекта медиа задаёт приложение.
     */
    protected function serializeMediaCollection(string $collection): array
    {
        if (! $this instanceof HasMedia) {
            return [];
        }

        /** @var SerializesMedia $serializer */
        $serializer = app(config('livewire-section-builder.media_serializer') ?? DefaultMediaSerializer::class);

        return $this->getMedia($collection)
            ->sortBy('order_column')
            ->map(fn (Media $media) => $serializer($media))
            ->values()
            ->all();
    }
}
