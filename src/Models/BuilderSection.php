<?php

namespace MountainClans\LivewireSectionBuilder\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use MountainClans\LaravelPolymorphicModel\Attributes\RequiresOverride;
use MountainClans\LaravelPolymorphicModel\Traits\PolymorphicModel;
use MountainClans\LivewireSectionBuilder\Traits\ResolvesApiFields;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

/**
 * @property string $id
 * @property string $template
 * @property string $page_id
 * @property string $type
 * @property int $order_column
 * @property \Spatie\SchemalessAttributes\SchemalessAttributes $fields не путать с импортированным классом каста
 */
class BuilderSection extends Model
{
    use HasUlids;
    use PolymorphicModel;
    use ResolvesApiFields;
    use SortableTrait;

    /**
     * Тип элементов репитера секции — переопределяется в секциях,
     * использующих репитеры (см. WithRepeaters::saveRepeaters()).
     */
    public const REPEATER_TYPE = null;

    protected $table = 'builder_sections';

    public array $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'template',
        'page_id',
        'type',
        'order_column',
        'fields',
    ];

    public $casts = [
        'fields' => SchemalessAttributes::class,
    ];

    public static function allowedTypes(): array
    {
        $registeredSections = config('livewire-section-builder.sections');

        return Arr::mapWithKeys($registeredSections, function (array $item, int $key) {
            return [$item['key'] => $item['model']];
        });
    }

    #[RequiresOverride]
    public function sectionTitle(): string
    {
        return '';
    }

    public function editorComponent(): ?string
    {
        return $this->registryValue('editor');
    }

    /**
     * null не только для незарегистрированной секции, но и для headless-секции
     * без Livewire-представления ('frontend' в реестре опционален).
     */
    public function frontendComponent(): ?string
    {
        return $this->registryValue('frontend');
    }

    /** Класс API-схемы секции из реестра ('schema', headless-режим). */
    public function apiSchema(): ?string
    {
        return $this->registryValue('schema');
    }

    /**
     * Карта «тип секции => класс API-схемы» по всему реестру — единая точка,
     * откуда достаются схемы контракта (кодген, аудит полноты oneOf и т.п.).
     * Секции без 'schema' (Livewire-режим) в карту не попадают.
     */
    public static function apiSchemas(): array
    {
        return collect(config('livewire-section-builder.sections', []))
            ->filter(fn (array $item) => ! empty($item['schema']))
            ->mapWithKeys(fn (array $item) => [$item['key'] => $item['schema']])
            ->all();
    }

    private function registryValue(string $configKey): ?string
    {
        $registeredSections = config('livewire-section-builder.sections');
        $templateSections = config('livewire-section-builder.templates');

        foreach ($templateSections[$this->template] ?? [] as $sectionKey) {
            if ($sectionKey === $this->type) {
                $sections = Arr::mapWithKeys($registeredSections, function (array $item, int $key) {
                    return [$item['key'] => $item];
                });

                return $sections[$this->type][$configKey] ?? null;
            }
        }

        return null;
    }

    /**
     * Секция для headless-витрины: schemaless-поля (переводимые — в заданной
     * локали) + репитеры по порядку. Медиа по умолчанию не включаются —
     * секция с картинками переопределяет метод и добавляет свои коллекции
     * через serializeMediaCollection().
     */
    public function toApiArray(string $locale): array
    {
        $data = $this->resolveApiFields($locale);

        $data['repeaters'] = $this->repeaters()
            ->orderBy('order_column')
            ->get()
            ->map(fn (BuilderSectionRepeater $repeater) => $repeater->toApiArray($locale))
            ->values()
            ->all();

        return $data;
    }

    public function scopeWithFields(): Builder
    {
        return $this->fields->modelScope();
    }

    /**
     * Элементы репитера секции. Секции с репитерами переопределяют связь
     * конкретной моделью; базовая реализация делает вызов безопасным
     * для любой секции (пустая коллекция вместо фатальной ошибки).
     */
    public function repeaters(): HasMany
    {
        return $this->hasMany(BuilderSectionRepeater::class, 'section_id');
    }
}
