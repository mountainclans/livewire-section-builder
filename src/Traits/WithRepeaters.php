<?php

namespace MountainClans\LivewireSectionBuilder\Traits;

use MountainClans\LivewireSectionBuilder\Models\BuilderSection;

/** @property BuilderSection $section */
trait WithRepeaters
{
    public array $repeaters = [];

    abstract protected function getRepeaterModel(): string;

    /**
     * Формат:
     * [
     *      'title',
     *      'description' => 'string',
     *      'is_active' => 'bool'
     * ]
     */
    abstract protected function getRepeaterFields(): array;

    /**
     * Возвращает массив дефолтных значений для нового репитера.
     * Можно переопределить в компоненте для редких кейсов.
     *
     * Формат:
     * [
     *   'limit' => 10,
     *   'query_type' => PropertiesTabRepeater::QUERY_TYPE_DISCOUNTS,
     *   'ad_type' => PropertyAdvertisement::SALE_FILTER_ID,
     * ]
     */
    protected function getRepeaterDefaults(): array
    {
        return [];
    }

    protected function normalizeFields(): array
    {
        $raw = $this->getRepeaterFields();

        $fields = [];
        foreach ($raw as $key => $value) {
            if (is_int($key)) {
                $fields[$value] = 'string';
            } else {
                $fields[$key] = $value;
            }
        }

        return $fields;
    }

    protected function setRepeaters(): void
    {
        $modelClass = $this->getRepeaterModel();
        $fields = $this->normalizeFields();

        $this->repeaters = $this->section->repeaters()
            ->get()
            ->map(function ($repeater) use ($fields, $modelClass) {
                $data = [
                    'id' => $repeater->id,
                    'is_deleted' => false,
                ];

                foreach ($fields as $field => $type) {
                    if ($this->isTranslatable($modelClass, "fields->{$field}")) {
                        $data[$field] = $repeater->getAllTranslations("fields->{$field}") ?? [];
                    } else {
                        $value = $repeater->fields->get($field);

                        $data[$field] = $value ?? $defaults[$field] ?? $this->defaultValueForType($type);
                    }
                }

                return $data;
            })
            ->toArray();
    }

    public function addRepeater(): void
    {
        $modelClass = $this->getRepeaterModel();
        $blankRepeater = new $modelClass();
        $fields = $this->normalizeFields();
        $defaults = $this->getRepeaterDefaults();

        $data = [
            'id' => '',
            'is_deleted' => false,
        ];

        foreach ($fields as $field => $type) {
            if ($this->isTranslatable($modelClass, "fields->{$field}")) {
                $data[$field] = $blankRepeater->getAllTranslations("fields->{$field}") ?? [];
            } else {
                $data[$field] = $defaults[$field] ?? $this->defaultValueForType($type);
            }
        }

        $index = count($this->repeaters);
        $this->repeaters[] = $data;

        // Автоматически инициализируем изображения, если трейт WithRepeaterImages подключен
        if (method_exists($this, 'initRepeaterImages')) {
            $this->initRepeaterImages($index);
        }
    }

    public function deleteRepeater(string $repeaterId): void
    {
        foreach ($this->repeaters as $index => &$repeater) {
            if ($repeater['id'] == $repeaterId) {
                // Автоматически очищаем изображения, если трейт WithRepeaterImages подключен
                if (method_exists($this, 'cleanupRepeaterImages')) {
                    $this->cleanupRepeaterImages($index);
                }

                $repeater['is_deleted'] = true;

                if (method_exists($this, 'info')) {
                    $this->info('The element was removed. Please, save the section for persist changes.');
                }

                return;
            }
        }
    }

    protected function saveRepeaters(): void
    {
        $modelClass = $this->getRepeaterModel();
        $fields = $this->normalizeFields();

        $this->section->repeaters()
            ->whereNotIn('id', array_filter(array_column($this->repeaters, 'id')))
            ->orWhereIn(
                'id',
                collect($this->repeaters)
                    ->where('is_deleted', true)
                    ->pluck('id')
                    ->all()
            )
            ->delete();

        $this->repeaters = array_values(array_filter($this->repeaters, fn($r) => !$r['is_deleted']));

        foreach ($this->repeaters as $index => $repeaterData) {
            $repeater = !empty($repeaterData['id'])
                ? $this->section->repeaters()->findOrFail($repeaterData['id'])
                : new $modelClass();

            $repeater->section_id = $this->section->id;
            $repeater->type = $this->section::REPEATER_TYPE ?? null;

            foreach ($fields as $field => $type) {
                if ($this->isTranslatable($modelClass, "fields->{$field}")) {
                    // при использовании setTranslations внутри Spatie иногда сбоит сохранение
                    $repeater->fields->set($field, $repeaterData[$field] ?? []);
                } else {
                    $repeater->fields->set($field, $this->castValueForStorage($repeaterData[$field] ?? null, $type));
                }
            }

            $repeater->save();

            // Обновляем ID в массиве repeaters для новых записей
            if (empty($repeaterData['id'])) {
                $this->repeaters[$index]['id'] = $repeater->id;
            }
        }

        // Автоматически сохраняем изображения репитеров, если трейт WithRepeaterImages подключен
        if (method_exists($this, 'persistRepeaterImages')) {
            $this->persistRepeaterImages();
        }

        $this->setRepeaters();
    }

    // --- Helpers ---

    /**
     * Проверяет, указано ли поле как translatable в модели репитера.
     * Ожидается, что в модели репитера в $translatable содержатся ключи вида 'fields->title'.
     */
    protected function isTranslatable(string $modelClass, string $key): bool
    {
        return in_array($key, (new $modelClass())->translatable ?? []);
    }

    protected function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int' => $value === null ? 0 : (int) $value,
            'float' => $value === null ? 0.0 : (float) $value,
            'bool' => (bool) $value,
            'array' => (array) ($value ?? []),
            default => (string) ($value ?? ''),
        };
    }

    protected function castValueForStorage(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => (bool) $value,
            'array' => (array) $value,
            default => $value, // string оставляем как есть — может быть '' или текст
        };
    }

    protected function defaultValueForType(string $type): mixed
    {
        return match ($type) {
            'int', 'float' => 0,
            'bool' => false,
            'array' => [],
            default => '', // string
        };
    }
}
