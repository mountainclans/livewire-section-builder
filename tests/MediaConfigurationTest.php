<?php

use MountainClans\LivewireSectionBuilder\Contracts\SerializesMedia;
use MountainClans\LivewireSectionBuilder\Models\BuilderSectionRepeater;
use MountainClans\LivewireSectionBuilder\Support\DefaultMediaSerializer;
use MountainClans\LivewireSectionBuilder\Traits\WithRepeaterImages;

it('дефолтный сериализатор медиа реализует контракт', function () {
    expect(app(DefaultMediaSerializer::class))->toBeInstanceOf(SerializesMedia::class);
});

it('классы медиа-моделей редактора картинок берутся из конфига', function () {
    config()->set('livewire-section-builder.media_model', 'App\Custom\Media');
    config()->set('livewire-section-builder.temp_media_model', 'App\Custom\TempMedia');

    $component = new class
    {
        use WithRepeaterImages;

        protected function getRepeaterModel(): string
        {
            return BuilderSectionRepeater::class;
        }

        protected function getRepeaterImagesCollection(): string
        {
            return 'images';
        }

        public function exposeMediaModel(): string
        {
            return $this->mediaModelClass();
        }

        public function exposeTempMediaModel(): string
        {
            return $this->tempMediaModelClass();
        }
    };

    expect($component->exposeMediaModel())->toBe('App\Custom\Media')
        ->and($component->exposeTempMediaModel())->toBe('App\Custom\TempMedia');
});
