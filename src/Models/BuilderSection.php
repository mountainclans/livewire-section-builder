<?php

namespace MountainClans\LivewireSectionBuilder\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use MountainClans\LaravelPolymorphicModel\Attributes\RequiresOverride;
use MountainClans\LaravelPolymorphicModel\Traits\PolymorphicModel;
use MountainClans\LivewireSectionBuilder\Exceptions\InvalidSectionTemplate;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

class BuilderSection extends Model
{
    use HasUlids;
    use PolymorphicModel;
    use SortableTrait;

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
        'is_visible',
        'fields',
    ];

    public $casts = [
        'fields' => SchemalessAttributes::class,
        'is_visible' => 'boolean',
    ];

    public static function allowedTypes(): array
    {
        $config = config('livewire-section-builder.templates');
        $result = [];

        foreach ($config as $sections) {
            foreach ($sections as $section) {
                $result[$section['key']] = $section['model'];
            }
        }

        return $result;
    }

    #[RequiresOverride]
    public function sectionTitle(): string
    {
        return '';
    }

    /**
     * @throws InvalidSectionTemplate
     */
    public function editorComponent(): string
    {
        $config = config('livewire-section-builder.templates');

        foreach ($config[$this->template] as $section) {
            if ($section['key'] === $this->type) {
                return $section['editor'];
            }
        }

        throw new InvalidSectionTemplate('Can`t find a editor template for this section.');
    }

    /**
     * @throws InvalidSectionTemplate
     */
    public function frontendComponent(): string
    {
        $config = config('livewire-section-builder.templates');

        foreach ($config[$this->template] as $section) {
            if ($section['key'] === $this->type) {
                return $section['frontend'];
            }
        }

        throw new InvalidSectionTemplate('Can`t find a frontend template for this section.');
    }

    public function scopeWithFields(): Builder
    {
        return $this->fields->modelScope();
    }
}
