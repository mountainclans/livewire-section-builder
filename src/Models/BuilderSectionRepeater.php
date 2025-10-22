<?php

namespace MountainClans\LivewireSectionBuilder\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use MountainClans\LaravelPolymorphicModel\Traits\PolymorphicModel;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

class BuilderSectionRepeater extends Model
{
    use HasUlids;
    use PolymorphicModel;

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
}
