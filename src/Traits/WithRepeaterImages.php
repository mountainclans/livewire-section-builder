<?php

namespace MountainClans\LivewireSectionBuilder\Traits;

use App\Models\Media;
use App\Models\TempMedia;
use Illuminate\Database\Eloquent\Model;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

trait WithRepeaterImages
{
    use WithFileUploads;

    public array $repeaterTempMedia = [];
    public array $uploadedRepeaterImages = [];

    public array $repeaterImages = [];
    public array $repeaterImageIdsForDelete = [];
    public array $repeaterImageIdsForOrdering = [];

    public function mountWithRepeaterImages(): void
    {
        // Инициализируем временные медиа для каждого репитера
        foreach ($this->repeaters as $index => $repeater) {
            $this->initRepeaterImagesForIndex($index);
        }
    }

    abstract protected function getRepeaterModel(): string;

    abstract protected function getRepeaterImagesCollection(): string;

    protected function maxRepeaterImagesCount(): int
    {
        return 1;
    }

    /**
     * Используем хук Livewire для обработки загруженных изображений репитера
     *
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function updatedUploadedRepeaterImages(): void
    {
        foreach ($this->uploadedRepeaterImages as $index => $images) {
            if (empty($images)) {
                continue;
            }

            $this->validate([
                "uploadedRepeaterImages.{$index}.*" => 'image|max:5012|mimes:jpg,jpeg,png,webp,svg'
            ]);

            // Убедимся, что максимальное количество изображений не превышено
            $max = $this->maxRepeaterImagesCount();
            $currentCount = count($this->repeaterImages[$index] ?? []);

            if ($max > 0 && $currentCount + count($images) > $max) {
                $this->addError("uploadedRepeaterImages.{$index}", "You can't upload more than {$max} images.");
                $this->uploadedRepeaterImages[$index] = [];
                continue;
            }

            foreach ($images as $image) {
                $image->store(path: 'images');

                $media = $this->repeaterTempMedia[$index]
                    ->addMedia($image)
                    ->withResponsiveImages()
                    ->toMediaCollection(TempMedia::COLLECTION_TEMP_IMAGES);

                if (!isset($this->repeaterImageIdsForOrdering[$index])) {
                    $this->repeaterImageIdsForOrdering[$index] = [];
                }

                $this->repeaterImageIdsForOrdering[$index][] = $media->id;
            }

            $this->uploadedRepeaterImages[$index] = [];
            $this->loadExistingRepeaterImages($index);
        }
    }

    public function deleteRepeaterImage(int $repeaterIndex, string $mediaId): void
    {
        $media = Media::find($mediaId);

        if ($media) {
            // Временные файлы удаляем насовсем
            if (
                isset($this->repeaterTempMedia[$repeaterIndex])
                && $media->model_type === TempMedia::class
                && $media->model_id === $this->repeaterTempMedia[$repeaterIndex]->id
            ) {
                $media->delete();
            }

            // Постоянные файлы помечаем как файлы к удалению
            $repeaterId = $this->repeaters[$repeaterIndex]['id'] ?? null;
            if ($repeaterId) {
                $repeaterModel = $this->getRepeaterModel()::find($repeaterId);
                if ($repeaterModel && $media->model_type === $this->getRepeaterModel() && $media->model_id === $repeaterModel->id) {
                    if (!isset($this->repeaterImageIdsForDelete[$repeaterIndex])) {
                        $this->repeaterImageIdsForDelete[$repeaterIndex] = [];
                    }
                    $this->repeaterImageIdsForDelete[$repeaterIndex][] = $mediaId;
                }
            }

            // Удаляем из списка на упорядочивание
            if (isset($this->repeaterImageIdsForOrdering[$repeaterIndex])) {
                $this->repeaterImageIdsForOrdering[$repeaterIndex] = array_filter(
                    $this->repeaterImageIdsForOrdering[$repeaterIndex],
                    function ($imageId) use ($repeaterIndex) {
                        return !in_array($imageId, $this->repeaterImageIdsForDelete[$repeaterIndex] ?? []);
                    }
                );
            }
        }

        $this->loadExistingRepeaterImages($repeaterIndex);
    }

    public function sortRepeaterImages(int $repeaterIndex, string $mediaId, int $position): void
    {
        if (!isset($this->repeaterImageIdsForOrdering[$repeaterIndex])) {
            return;
        }

        $currentIndex = array_search($mediaId, $this->repeaterImageIdsForOrdering[$repeaterIndex]);
        if ($currentIndex === false) {
            return;
        }

        unset($this->repeaterImageIdsForOrdering[$repeaterIndex][$currentIndex]);

        $this->repeaterImageIdsForOrdering[$repeaterIndex] = array_values($this->repeaterImageIdsForOrdering[$repeaterIndex]);
        array_splice($this->repeaterImageIdsForOrdering[$repeaterIndex], $position, 0, $mediaId);

        $this->loadExistingRepeaterImages($repeaterIndex);
    }

    public function persistRepeaterImages(): void
    {
        $this->clearValidation();

        foreach ($this->repeaters as $index => $repeaterData) {
            $repeaterId = $repeaterData['id'] ?? null;

            if (!$repeaterId) {
                continue;
            }

            /** @var Model&HasMedia $repeater */
            $repeater = $this->getRepeaterModel()::find($repeaterId);

            if (!$repeater) {
                continue;
            }

            // Удаляем помеченные изображения
            if (isset($this->repeaterImageIdsForDelete[$index])) {
                foreach ($this->repeaterImageIdsForDelete[$index] as $imageId) {
                    $image = Media::find($imageId);

                    if ($image && $image->model_type === $this->getRepeaterModel() && $image->model_id === $repeater->id) {
                        $image->delete();
                    }
                }
            }

            // Устанавливаем порядок
            if (isset($this->repeaterImageIdsForOrdering[$index])) {
                Media::setNewOrder($this->repeaterImageIdsForOrdering[$index]);
            }

            // Перемещаем временные изображения к репитеру
            if (isset($this->repeaterTempMedia[$index])) {
                /** @var Media $image */
                foreach ($this->repeaterTempMedia[$index]->getMedia(TempMedia::COLLECTION_TEMP_IMAGES) as $image) {
                    $image->move($repeater, $this->getRepeaterImagesCollection());
                }
            }
        }
    }

    private function loadExistingRepeaterImages(int $repeaterIndex): void
    {
        $images = [];

        $repeaterId = $this->repeaters[$repeaterIndex]['id'] ?? null;

        if ($repeaterId) {
            /** @var Model&HasMedia $repeater */
            $repeater = $this->getRepeaterModel()::find($repeaterId);

            if ($repeater) {
                $images = $repeater
                    ->getMedia($this->getRepeaterImagesCollection())
                    ->filter(fn($media) => $media instanceof Media)
                    ->map(fn(Media $media) => [
                        'id' => $media->id,
                        'url' => $media->getUrl(),
                        'admin_preview' => $media->getUrl('admin_preview'),
                        'full' => $media->getUrl(),
                    ])->toArray();
            }
        }

        // Добавляем временные изображения
        if (isset($this->repeaterTempMedia[$repeaterIndex])) {
            $tempImages = $this->repeaterTempMedia[$repeaterIndex]
                ->getMedia(TempMedia::COLLECTION_TEMP_IMAGES)
                ->filter(fn($media) => $media instanceof Media)
                ->map(fn(Media $media) => [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'admin_preview' => $media->getUrl('admin_preview'),
                    'full' => $media->getUrl(),
                ])
                ->toArray();

            $images = array_merge($images, $tempImages);
        }

        // Удалим из списка изображения, помеченные для удаления
        if (isset($this->repeaterImageIdsForDelete[$repeaterIndex]) && !empty($this->repeaterImageIdsForDelete[$repeaterIndex])) {
            $images = array_filter($images, function ($image) use ($repeaterIndex) {
                return !in_array($image['id'], $this->repeaterImageIdsForDelete[$repeaterIndex]);
            });
        }

        // Отсортируем по установленному порядку
        if (isset($this->repeaterImageIdsForOrdering[$repeaterIndex])) {
            usort($images, function ($a, $b) use ($repeaterIndex) {
                $indexA = array_search($a['id'], $this->repeaterImageIdsForOrdering[$repeaterIndex]);
                $indexB = array_search($b['id'], $this->repeaterImageIdsForOrdering[$repeaterIndex]);

                return $indexA - $indexB;
            });
        }

        $this->repeaterImages[$repeaterIndex] = $images;
    }

    /**
     * Инициализирует изображения для конкретного индекса репитера
     */
    private function initRepeaterImagesForIndex(int $index): void
    {
        if (!isset($this->repeaterTempMedia[$index])) {
            $tempMedia = new TempMedia();
            $tempMedia->for_model = $this->getRepeaterModel();
            $tempMedia->save();

            $this->repeaterTempMedia[$index] = $tempMedia;
        }

        $this->loadExistingRepeaterImages($index);

        // установим порядок изображений для репитера
        if (!isset($this->repeaterImageIdsForOrdering[$index])) {
            $this->repeaterImageIdsForOrdering[$index] = [];
        }

        foreach ($this->repeaterImages[$index] ?? [] as $image) {
            if (!in_array($image['id'], $this->repeaterImageIdsForOrdering[$index])) {
                $this->repeaterImageIdsForOrdering[$index][] = $image['id'];
            }
        }
    }

    /**
     * Вызывается при добавлении нового репитера
     * Публичный метод для использования в WithRepeaters
     */
    public function initRepeaterImages(int $index): void
    {
        $this->initRepeaterImagesForIndex($index);
    }

    /**
     * Вызывается при удалении репитера
     * Публичный метод для использования в WithRepeaters
     */
    public function cleanupRepeaterImages(int $index): void
    {
        // Удаляем временные медиа
        if (isset($this->repeaterTempMedia[$index])) {
            $this->repeaterTempMedia[$index]->clearMediaCollection(TempMedia::COLLECTION_TEMP_IMAGES);
            $this->repeaterTempMedia[$index]->delete();
            unset($this->repeaterTempMedia[$index]);
        }

        // Очищаем массивы
        unset($this->repeaterImages[$index]);
        unset($this->repeaterImageIdsForOrdering[$index]);
        unset($this->repeaterImageIdsForDelete[$index]);
        unset($this->uploadedRepeaterImages[$index]);

        // Переиндексируем массивы
        $this->repeaterTempMedia = array_values($this->repeaterTempMedia);
        $this->repeaterImages = array_values($this->repeaterImages);
        $this->repeaterImageIdsForOrdering = array_values($this->repeaterImageIdsForOrdering);
        $this->repeaterImageIdsForDelete = array_values($this->repeaterImageIdsForDelete);
        $this->uploadedRepeaterImages = array_values($this->uploadedRepeaterImages);
    }
}
