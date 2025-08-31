@use('MountainClans\LivewireSectionBuilder\Models\BuilderSection')

<div class="w-full p-4 rounded-lg my-5 border border-gray-500 dark:border-gray-700">
    {{-- Список секций и их редакторы --}}
    <div class="mb-3">
        <h2 class="dark:text-white font-semibold mb-4">
            {{ __('livewire-section-builder::interface.sections') }}
        </h2>
    </div>

    @if(!$sectionModels->isEmpty())
        <div x-sort="$wire.sortSections($item, $position)"
             class="mx-auto mb-12"
        >
            @foreach ($sectionModels as $sectionModel)
                @php
                    /** !!! Важно обернуть разнородные компоненты в div с постоянным ключом !!! */
                    /** @var BuilderSection $section */
                @endphp

                <div x-sort:item="'{{ $sectionModel->id }}'"
                     wire:key="{{ $sectionModel->type . '-' . $sectionModel->id }}"
                     class="w-full border border-gray-300 dark:border-gray-600 border-2 rounded-lg p-4 mb-6"
                >
                    <div class="flex justify-between">
                        {{-- Sort handler --}}
                        <div x-sort:handle
                             class="flex items-center me-4"
                        >
                            <svg class="w-6 h-6 text-gray-800 dark:text-white flex-shrink-0 cursor-grab"
                                 aria-hidden="true"
                                 xmlns="http://www.w3.org/2000/svg"
                                 width="24"
                                 height="24"
                                 fill="none"
                                 viewBox="0 0 24 24"
                            >
                                <path stroke="currentColor"
                                      stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="m8 15 4 4 4-4m0-6-4-4-4 4"
                                ></path>
                            </svg>
                        </div>

                        {{-- Section title --}}
                        <div
                            class="p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-700 dark:text-blue-400 flex-grow">
                            {{ $sectionModel->sectionTitle() }}
                        </div>

                        {{-- Section actions --}}
                        <div class="flex-grow-0 flex items-center ms-4">
                            {{-- Hide / show section --}}
                            <button wire:click="toggleSectionVisibility('{{ $sectionModel->id }}')"
                                    class="w-6 h-6 ms-4"
                            >
                                @if ($sectionModel->is_visible)
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white"
                                         aria-hidden="true"
                                         xmlns="http://www.w3.org/2000/svg"
                                         width="24"
                                         height="24"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                    >
                                        <path stroke="currentColor"
                                              stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M7.757 12h8.486M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                        />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white"
                                         aria-hidden="true"
                                         xmlns="http://www.w3.org/2000/svg"
                                         width="24"
                                         height="24"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                    >
                                        <path stroke="currentColor"
                                              stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M12 7.757v8.486M7.757 12h8.486M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                        />
                                    </svg>
                                @endif
                            </button>

                            <button wire:click="deleteSection('{{ $sectionModel->id }}')"
                                    wire:confirm="{{ __('livewire-section-builder::interface.sure_delete_this_section') }}"
                                    class="w-6 h-6 ms-4"
                            >
                                <svg class="w-6 h-6 text-red-800 dark:text-red-600"
                                     aria-hidden="true"
                                     xmlns="http://www.w3.org/2000/svg"
                                     width="24"
                                     height="24"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                >
                                    <path stroke="currentColor"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    @if ($sectionModel->is_visible)
                        <livewire:is :component="$sectionModel->editorComponent()"
                                     :section="$sectionModel"
                                     wire:key="{{ $sectionModel->id }}"
                        />
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-700 dark:text-blue-400"
             role="alert">
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
