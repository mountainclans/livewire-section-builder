# Livewire Section Builder

## Установка

Установите пакет при помощи composer:

```bash
composer require mountainclans/livewire-section-builder
```

**Важно!** Пакет зависит от других компонентов, в частности, необходимо сразу после установки этого пакета также сконфигурировать для использования пакет [mountainclans/livewire-select](https://github.com/mountainclans/livewire-select) (инструкции находятся на его странице).

Опубликуйте и примените миграции:

```bash
php artisan vendor:publish --tag="livewire-section-builder-migrations"
php artisan migrate
```

Опубликуйте конфигурационный файл:

```bash
php artisan vendor:publish --tag="livewire-section-builder-config"
```

Конфигурационный массив имеет следующую вложенность:
```php
retyrn [
    'sections' => [
        [
            'key' => 'top_banner',
            'title' => 'Top banner',
            'model' => EloquentSectionModelClass::class,
            'editor' => LivewireEditorComponent::class,
            'frontend' => LivewireViewComponent::class,
        ],
        [
            'key' => 'advantages',
            'title' => 'Advantages',
            'model' => EloquentSectionModelClass2::class,
            'editor' => LivewireEditorComponent2::class,
            'frontend' => LivewireViewComponent2::class,
        ],
    ],
    'templates' => [
        'main_page' => [
            'top_banner',
            'advantages',
        ],
    ],
]
```

---

Добавьте в tailwind.config.js в секцию content:

```js
'./vendor/mountainclans/livewire-section-builder/resources/views/**/*.blade.php'
```

Опционально, Вы можете опубликовать `views` для их переопределения:

```bash
php artisan vendor:publish --tag="livewire-section-builder-views"
```

## Использование

Перед использованием компонентов пакета, пожалуйста, сконфигурируйте используемые классы в конфигурационном файле.

### Модель секции
Модель секции должна расширять класс

Структура хранения модели состоит из следующих полей:
- id
- template
- page_id
- type
- order_column
- fields
- created_at
- updated_at

Не рекомендуется менять миграцию, создающую таблицу для секций. Все необходимые поля секции можно сохранить в атрибуте модели `fields`. Данный атрибут представляет собой [schemaless attribute, реализация от Spatie](https://github.com/spatie/laravel-schemaless-attributes).

Тем не менее, Вы вправе расширять модель секции как Вам необходимо, к примеру, использовать трейты `HasTranslations`, `InteractsWithMedia` и другие.

Для того, чтобы одна и та же секция могла быть доступна для разных шаблонов, пожалуйста, используйте **уникальные** ключи в качестве названий секций.

### Админка
В нужном месте страницы используйте компонент редактора:

```bladehtml
@if ($pageId && !empty($destination))
    <livewire:admin-section-builder :template="BuilderPage::DESTINATION_HOME_PAGE" 
                                    :page-id="$pageId"
    />
@endif
```

Компоненты `editor` сами занимаются валидацией и сохранением своего состояния, в них может быть любая логика. Удалением, сортировкой, скрытием и показом контента этих компонентов занимается компонент `admin-section-builder`. 

Настоятельно рекомендуется, чтобы компонент взаимодействовал только с соответствующей моделью, указанной в конфиге как `model`.

Рекомендуется создавать как минимум один публичный метод - `saveSection`, но в целом реализация никак не регламентирована.

При сохранении компонента рекомендуется выбрасывать событие:

```php
$this->dispatch(AdminSectionBuilder::EVENT_SECTION_UPDATED);
```

#### События при редактировании в админке

Вы можете расширить класс компонента `AdminSectionBuilder` и использовать собственный компонент в админке. В частности, Вы можете подписаться на одно из следующих событий:

-  AdminSectionBuilder::EVENT_SECTION_ADDED
-  AdminSectionBuilder::EVENT_SECTION_UPDATED
-  AdminSectionBuilder::EVENT_SECTION_SORTED
-  AdminSectionBuilder::EVENT_SECTION_HIDDEN
-  AdminSectionBuilder::EVENT_SECTION_SHOWED
-  AdminSectionBuilder::EVENT_SECTION_DELETED

И реализовать, к примеру, отправку уведомлений пользователю.

## View-компонент

```bladehtml
<livewire:frontend-section-viewer :page-id="$page->id"
                                  :template="BuilderPage::DESTINATION_HOME_PAGE"
/>
```

Данный компонент выведет все секции с нужным контентом и в нужном порядке.

## Повторители

Иногда при создании секции необходимо повторить в ней однотипные данные несколько раз. Сделать это призвана комбинация трейта `WithRepeaters` и использование blade-компонента `admin.repeater-editor`.

Создайте модель-наследника `BuilderSectionRepeater` из пакета. Переопределите метод `allowedTypes`. Используйте данную модель как базовую, от которой будут наследоваться все повторители секций.

```php
use MountainClans\LivewireSectionBuilder\Models\BuilderSectionRepeater as BaseBuilderSectionRepeater;

class BuilderSectionRepeater extends BaseBuilderSectionRepeater
{
    public static function allowedTypes(): array
    {
        return [
            PropertiesSliderSection::REPEATER_TYPE => PropertiesTabRepeater::class,
        ];
    }
}
```

Унаследуйтесь от этой модели для создания уникальных классов-повторителей.  Добавьте в них необходимые трейты (к примеру, `HasTranslations`, `FilledTranslatableFields`).

Если модель использует `HasTranslations`, укажите список переводимых полей.

```php
class AdvantagesOneRepeater extends BuilderSectionRepeater
{
    protected function getInstanceType(): string
    {
        return AdvantagesOneSection::REPEATER_TYPE;
    }

    public array $translatable = [
        'fields->title',
        'fields->description',
    ];
}
```

### Настройка секции, использующей повторитель
- добавьте в класс секции, использующей repeater, константу `REPEATER_TYPE`;
- добавьте метод, реализующий связь `repeaters` (HasMany):

```php
class AdvantagesOneSection extends BuilderSection
{
    public const REPEATER_TYPE = 'advantages_1_repeater';

    public function repeaters(): HasMany
    {
        return $this->hasMany(AdvantagesOneRepeater::class, 'section_id');
    }
}

```

### Редактор
В редакторе секции используйте трейт `WithRepeaters`. 

**Обязательно** переопределите методы редактора секции `getRepeaterModel` и `getRepeaterFields`, как показано в примере.

**Опционально** вы можете использовать метод `getRepeaterDefaults` для того, чтобы задать начальные значения полей.

```php
class AdvantagesOneSectionEditor extends Component
{
    // другие трейты
    use WithRepeaters;

    // поля компонента

    public AdvantagesOneSection $section;

    public function mount(AdvantagesOneSection $section): void
    {
        // инициализационная логика

        $this->setRepeaters();
    }

    public function saveSection(): void
    {
        // логика сохранения основного контента
        $this->saveRepeaters();
       
        // другая логика
        $this->dispatch(AdminSectionBuilder::EVENT_SECTION_UPDATED);
    }

    protected function getRepeaterModel(): string
    {
        return AdvantagesOneRepeater::class;
    }

    protected function getRepeaterFields(): array
    {
        return ['title', 'description'];
    }
    
    // другая логика компонента
}
```

Во `view` используйте компонент-обёртку для создания редактора контента повторителей. Пример ниже:

```bladehtml
{{-- Repeaters --}}
    <div class="w-full border-gray-300 dark:border-purple-800 border-2 rounded-lg p-4 mb-6">
        <h2 class="dark:text-white font-semibold mb-4">{{ __('Advantages') }}</h2>

        @empty($repeaters)
            <div class="p-4 text-sm text-gray-800 rounded-lg bg-gray-50 dark:bg-gray-700 dark:text-gray-300"
                 role="alert">
                {{ __('Not exists yet') }}
            </div>
        @else
            @foreach($repeaters as $index => $repeater)
                <x-admin.repeater-editor :repeater="$repeater" :index="$index">
                    <x-ui.translatable>
                        <div class="grid gap-4 grid-cols-1 lg:grid-cols-5 lg:gap-6">
                            <div class="col-span-1">
                                <x-ui.input wire:model="repeaters.{{ $index }}.title"
                                            translatable
                                            :placeholder="__('Title')"
                                            :label="__('Title')"
                                />
                            </div>
                            <div class="col-span-4">
                                <x-ui.tiptap wire:model="repeaters.{{ $index }}.description"
                                             translatable
                                             height="70"
                                             placeholder="{{ __('Description') }}"
                                             label="{{ __('Description') }}"
                                />
                            </div>
                        </div>
                    </x-ui.translatable>
                </x-admin.repeater-editor>
            @endforeach
        @endempty
    </div>

    <div class="flex justify-between items-center">
        <button wire:click="addRepeater"
                type="button"
                class="cursor-pointer focus:outline-none text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 mb-2 dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-900"
        >{{ __('Add advantage') }}</button>

        <x-ui.submit-button>{{ __('Save section') }}</x-ui.submit-button>
    </div>
```

## Авторы

- [Vladimir Bajenov](https://github.com/mountainclans)
- [All Contributors](../../contributors)

## Лицензия

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
