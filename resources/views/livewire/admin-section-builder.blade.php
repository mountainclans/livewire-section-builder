@use('MountainClans\LivewireSectionBuilder\Models\BuilderSection')

<div class="w-full p-4 rounded-lg my-5 border border-gray-500 dark:border-gray-700">
    {{-- Список секций и их редакторы --}}
    <div class="mb-3">
        <h2 class="dark:text-white font-semibold mb-4">
            {{ __('livewire-section-builder::interface.sections') }}
        </h2>
    </div>

    @if(!$sectionModels->isEmpty())
        <div
            {{--            x-sort="$wire.sortSections($item, $position)"--}}
            class="mx-auto mb-12"
        >
            @foreach ($sectionModels as $sectionModel)
                @php /** @var BuilderSection $section */ @endphp
                {{-- !!! Важно обернуть разнородные компоненты в div с постоянным ключом !!! --}}
                {{-- TODO перетаскиватель - только маленькая иконка вместо блока --}}
                <div x-sort:item="'{{ $sectionModel->id }}'"
                     wire:key="{{ $sectionModel->type . '-' . $sectionModel->id }}"
                     class="key-wrapper"
                >
                    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-600 dark:text-green-400">
                        {{ $sectionModel->sectionTitle() }}
                    </div>

                    <livewire:is :component="$sectionModel->editorComponent()"
                                 :section="$sectionModel"
                                 wire:key="{{ $sectionModel->id }}"
                    />
                </div>
            @endforeach
        </div>
    @else
        <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-700 dark:text-blue-400" role="alert">
            {{ __('livewire-section-builder::interface.sections_list_is_empty_yet') }}
        </div>
    @endif
    {{-- /Список секций и их редакторы --}}

    {{-- Добавление секции --}}
    <div class="mt-7 mb-3 br-blue-300">
        <h3 class="dark:text-white font-semibold mb-4">
            {{ __('livewire-section-builder::interface.add_section') }}
        </h3>
    </div>

    @if (!empty ($availableSections))
        <form wire:submit="addSection"
              id="addSection"
              class="mt-5"
        >
            <div class="grid grid-cols lg:grid-cols-3 gap-4 mb-5 pt-4">
                <div class="w-full">
                    <x-ui.select wire:model="newSectionType"
                                 label="{{ __('livewire-section-builder::interface.type_of_section_to_add') }}"
                                 placeholder="{{ __('livewire-section-builder::interface.select_type_of_section_to_add') }}"
                                 :values="$availableSections"
                                 :hide-search="true"
                    />
                </div>

                <div class="w-full flex items-end">
                    <x-ui.submit-button>{{ __('livewire-section-builder::interface.add') }}</x-ui.submit-button>
                </div>
            </div>
        </form>
    @endif
    {{-- /Добавление секции --}}
</div>
