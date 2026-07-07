<?php

namespace MountainClans\LivewireSectionBuilder\Tests\Fixtures;

use Illuminate\Database\Eloquent\Relations\HasMany;
use MountainClans\LivewireSectionBuilder\Models\BuilderSection;

class ApiSection extends BuilderSection
{
    public array $translatable = ['fields->title'];

    protected function getInstanceType(): string
    {
        return 'api_section';
    }

    public function sectionTitle(): string
    {
        return 'API section';
    }

    public function repeaters(): HasMany
    {
        return $this->hasMany(ApiSectionRepeater::class, 'section_id');
    }
}
