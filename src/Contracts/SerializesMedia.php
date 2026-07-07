<?php

namespace MountainClans\LivewireSectionBuilder\Contracts;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Сериализатор медиа для API-выдачи секций. Приложение задаёт свой формат
 * объекта медиа, реализуя контракт и указывая класс в конфиге
 * 'livewire-section-builder.media_serializer'.
 */
interface SerializesMedia
{
    public function __invoke(Media $media): array;
}
