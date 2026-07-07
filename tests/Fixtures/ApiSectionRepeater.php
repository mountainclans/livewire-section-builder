<?php

namespace MountainClans\LivewireSectionBuilder\Tests\Fixtures;

use MountainClans\LivewireSectionBuilder\Models\BuilderSectionRepeater;

class ApiSectionRepeater extends BuilderSectionRepeater
{
    public array $translatable = ['fields->label'];

    protected function getInstanceType(): string
    {
        return 'api_section_repeater';
    }
}
