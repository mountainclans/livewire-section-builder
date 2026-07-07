<?php

namespace MountainClans\LivewireSectionBuilder\Support;

use MountainClans\LivewireSectionBuilder\Contracts\SerializesMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DefaultMediaSerializer implements SerializesMedia
{
    public function __invoke(Media $media): array
    {
        return [
            'url' => $media->getUrl(),
            'alt' => $media->getCustomProperty('alt'),
        ];
    }
}
