<?php

namespace MountainClans\LivewireSectionBuilder\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MountainClans\LaravelPolymorphicModel\Traits\PolymorphicModel;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

class BuilderSection extends Model
{
    use PolymorphicModel;
    use SortableTrait;

    protected $table = 'builder_sections';

    public array $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'sections_set',
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
        $config = config('livewire-section-builder.sections_sets');
        $result = [];

        foreach ($config as $setKey => $sections) {
            foreach ($sections as $section) {
                $result[$setKey . '_' . $section['key']] = $section['model'];
            }
        }

        return $result;
    }

    public function scopeWithFields(): Builder
    {
        return $this->fields->modelScope();
    }
}
