<?php

namespace MountainClans\LivewireSectionBuilder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use MountainClans\LaravelPolymorphicModel\Traits\PolymorphicModel;
use MountainClans\LivewireSectionBuilder\Traits\ResolvesApiFields;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

/**
 * @property string $id
 * @property string $section_id
 * @property string $type
 * @property int $order_column
 * @property \Spatie\SchemalessAttributes\SchemalessAttributes $fields не путать с импортированным классом каста
 *
 * @method array getAllTranslations(string $key) предоставляется трейтом FilledTranslatableFields (mountainclans/livewire-translatable) в моделях приложения
 */
class BuilderSectionRepeater extends Model
{
    use HasUlids;
    use PolymorphicModel;
    use ResolvesApiFields;

    protected $table = 'builder_section_repeaters';

    protected $fillable = [
        'type',
        'section_id',
        'order_column',
        'fields',
    ];

    public $casts = [
        'fields' => SchemalessAttributes::class,
    ];

    public static function allowedTypes(): array
    {
        return [];
    }

    /** Элемент репитера в API-выдаче: id + schemaless-поля в заданной локали. */
    public function toApiArray(string $locale): array
    {
        return ['id' => $this->id] + $this->resolveApiFields($locale);
    }
}
