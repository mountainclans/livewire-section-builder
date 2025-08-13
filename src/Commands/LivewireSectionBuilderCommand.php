<?php

namespace MountainClans\LivewireSectionBuilder\Commands;

use Illuminate\Console\Command;

class LivewireSectionBuilderCommand extends Command
{
    public $signature = 'livewire-section-builder';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
