<?php

use MountainClans\LivewireSectionBuilder\Traits\WithRepeaterImages;
use MountainClans\LivewireSectionBuilder\Traits\WithRepeaters;

/**
 * Перетаскивание карточки должно двигать и параллельные массивы состояния
 * картинок: они индексируются позицией карточки в редакторе.
 */
function sortableEditor(): object
{
    return new class
    {
        use WithRepeaterImages;
        use WithRepeaters;

        public array $repeaters = [];

        protected function getRepeaterModel(): string
        {
            return stdClass::class;
        }

        protected function getRepeaterFields(): array
        {
            return ['title'];
        }

        protected function getRepeaterImagesCollection(): string
        {
            return 'images';
        }
    };
}

it('переставляет репитеры вместе с плотными массивами картинок', function () {
    $editor = sortableEditor();
    $editor->repeaters = [['id' => 'a'], ['id' => 'b'], ['id' => 'c']];
    $editor->repeaterImages = [0 => ['a.jpg'], 1 => ['b.jpg'], 2 => ['c.jpg']];

    // Тащим последнюю карточку в начало
    $editor->sortRepeaters(2, 0);

    expect(array_column($editor->repeaters, 'id'))->toBe(['c', 'a', 'b'])
        ->and($editor->repeaterImages)->toBe([0 => ['c.jpg'], 1 => ['a.jpg'], 2 => ['b.jpg']]);
});

it('сохраняет пропуски в разреженных массивах загрузок и удалений', function () {
    $editor = sortableEditor();
    $editor->repeaters = [['id' => 'a'], ['id' => 'b'], ['id' => 'c']];
    // Тронута только третья карточка: у остальных ключей нет вовсе
    $editor->repeaterImageIdsForDelete = [2 => ['media-c']];
    $editor->uploadedRepeaterImages = [2 => ['upload-c']];

    $editor->sortRepeaters(2, 0);

    expect($editor->repeaterImageIdsForDelete)->toBe([0 => ['media-c']])
        ->and($editor->uploadedRepeaterImages)->toBe([0 => ['upload-c']]);
});

it('двигает карточку вниз, не задевая соседей', function () {
    $editor = sortableEditor();
    $editor->repeaters = [['id' => 'a'], ['id' => 'b'], ['id' => 'c'], ['id' => 'd']];
    $editor->repeaterImages = [['a'], ['b'], ['c'], ['d']];

    $editor->sortRepeaters(0, 2);

    expect(array_column($editor->repeaters, 'id'))->toBe(['b', 'c', 'a', 'd'])
        ->and($editor->repeaterImages)->toBe([['b'], ['c'], ['a'], ['d']]);
});

it('на несуществующем индексе ничего не меняет', function () {
    $editor = sortableEditor();
    $editor->repeaters = [['id' => 'a']];
    $editor->repeaterImages = [0 => ['a.jpg']];

    $editor->sortRepeaters(5, 0);

    expect(array_column($editor->repeaters, 'id'))->toBe(['a'])
        ->and($editor->repeaterImages)->toBe([0 => ['a.jpg']]);
});
