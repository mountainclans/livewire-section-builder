<?php

namespace MountainClans\LivewireSectionBuilder\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MountainClans\LaravelPolymorphicModel\Attributes\RequiresOverride;
use MountainClans\LaravelPolymorphicModel\Traits\PolymorphicModel;
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
        $registeredSections = config('livewire-section-builder.sections');
        $templateSections = config('livewire-section-builder.templates');

        foreach ($templateSections[$this->template] as $sectionKey) {
            if ($sectionKey === $this->type) {
                $sections = Arr::mapWithKeys($registeredSections, function (array $item, int $key) {
                    return [$item['key'] => $item];
                });

                return $sections[$this->type]['editor'];
            }
        }

        return null;
    }

    public function frontendComponent(): ?string
    {
        $registeredSections = config('livewire-section-builder.sections');
        $templateSections = config('livewire-section-builder.templates');

        foreach ($templateSections[$this->template] as $sectionKey) {
            if ($sectionKey === $this->type) {
                $sections = Arr::mapWithKeys($registeredSections, function (array $item, int $key) {
                    return [$item['key'] => $item];
                });

                return $sections[$this->type]['frontend'];
            }
        }

        return null;
    }

    public function scopeWithFields(): Builder
    {
        return $this->fields->modelScope();
    }
}
