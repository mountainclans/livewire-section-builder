<?php

namespace MountainClans\LivewireSectionBuilder\Support;

use InvalidArgumentException;

/**
 * Проверка реестра секций (opt-in: config 'validate_registry').
 * В headless-режиме ('headless' => true) вместо 'frontend' обязателен
 * 'schema' — класс API-схемы секции: реестр становится единым местом,
 * откуда достаются все схемы контракта.
 */
class RegistryValidator
{
    public function __invoke(): void
    {
        if (! config('livewire-section-builder.validate_registry', false)) {
            return;
        }

        $required = ['key', 'title', 'model', 'editor'];

        if (config('livewire-section-builder.headless', false)) {
            $required[] = 'schema';
        } else {
            $required[] = 'frontend';
        }

        foreach (config('livewire-section-builder.sections', []) as $index => $section) {
            foreach ($required as $key) {
                if (empty($section[$key])) {
                    $name = $section['key'] ?? "#{$index}";

                    throw new InvalidArgumentException(
                        "livewire-section-builder: section '{$name}' is missing required config key '{$key}'."
                    );
                }
            }
        }

        $known = array_column(config('livewire-section-builder.sections', []), 'key');

        foreach (config('livewire-section-builder.templates', []) as $template => $sectionKeys) {
            foreach ((array) $sectionKeys as $sectionKey) {
                if (! in_array($sectionKey, $known, true)) {
                    throw new InvalidArgumentException(
                        "livewire-section-builder: template '{$template}' references unknown section '{$sectionKey}'."
                    );
                }
            }
        }
    }
}
