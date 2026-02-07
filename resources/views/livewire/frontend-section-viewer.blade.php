@use('MountainClans\LivewireSectionBuilder\Models\BuilderSection')

<div class="w-full">
    @foreach ($sectionModels as $sectionModel)
        @php
            /** !!! Важно обернуть разнородные компоненты в div с постоянным ключом !!! */
            /** @var BuilderSection $sectionModel */
        @endphp

        <div wire:key="{{ $sectionModel->type . '-' . $sectionModel->id }}"
             class="{{ $sectionModel->id }}_section"
        >
            <livewire:is :component="$sectionModel->frontendComponent()"
                         :section="$sectionModel"
                         wire:key="{{ $sectionModel->id }}"
            />
        </div>
    @endforeach
</div>
