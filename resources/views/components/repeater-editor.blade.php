{{-- $repeater — элемент массива WithRepeaters::$repeaters (не модель): id, is_deleted + поля. --}}
@php /** @var array{id: string, is_deleted: bool} $repeater */ @endphp
<div @class([
        'p-4 pr-20 w-full mb-4 relative border border-blue-400 rounded-xl',
        'hidden' => $repeater['is_deleted']
    ])
     wire:key="repeater_{{ $index }}"
     x-data="sbCollapsible('{{ !empty($repeater['id']) ? 'sb-collapsed:repeater:' . $repeater['id'] : '' }}')"
>
    @include('livewire-section-builder::components.collapsible-script')

    {{-- Свёрнутое состояние: компактная плашка вместо полей --}}
    <div x-show="collapsed"
         x-cloak
         x-on:click="toggle()"
         class="text-sm text-gray-500 dark:text-gray-400 select-none cursor-pointer py-1"
    >
        {{ __('livewire-section-builder::interface.element_number', ['number' => $index + 1]) }}
    </div>

    <div x-show="!collapsed">
        {{ $slot }}
    </div>

    {{-- Кнопка сворачивания --}}
    <button x-on:click="toggle()"
            class="absolute right-11 top-3 w-6 h-6 cursor-pointer"
            type="button"
    >
        <svg x-show="!collapsed"
             class="w-6 h-6 text-gray-800 dark:text-white"
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
                  d="m16 14-4-4-4 4"
            ></path>
        </svg>
        <svg x-show="collapsed"
             x-cloak
             class="w-6 h-6 text-gray-800 dark:text-white"
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
                  d="m8 10 4 4 4-4"
            ></path>
        </svg>
    </button>

    {{-- Кнопка удаления --}}
    <button wire:click="deleteRepeater('{{ $repeater['id'] }}')"
            wire:confirm="Are you sure you want to delete this element?"
            class="absolute right-3 top-3 w-6 h-6 cursor-pointer"
            type="button"
    >
        <svg class="w-6 h-6 text-red-800 dark:text-red-600" aria-hidden="true"
             xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
        </svg>
    </button>
</div>
