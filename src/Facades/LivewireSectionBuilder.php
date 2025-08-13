<?php

namespace MountainClans\LivewireSectionBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MountainClans\LivewireSectionBuilder\LivewireSectionBuilder
 */
class LivewireSectionBuilder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MountainClans\LivewireSectionBuilder\LivewireSectionBuilder::class;
    }
}
