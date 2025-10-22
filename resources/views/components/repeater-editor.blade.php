@use('App\Models\BuilderSectionRepeater')

@php /** @var BuilderSectionRepeater $repeater */ @endphp
<div @class([
        'p-4 pr-12 w-full mb-4 relative border border-blue-400 rounded-xl',
        'hidden' => $repeater['is_deleted']
    ])
     wire:key="repeater_{{ $index }}"
>
    {{ $slot }}

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

