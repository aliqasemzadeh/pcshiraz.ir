@props([
    'row',
    'editModal',
    'viewModal',
    'idProp',
])

@php
    $rowId = $row->getKey();
@endphp

<div class="flex items-center gap-2">
    <x-fwb.tooltip :id="'pg-tooltip-view-'.$rowId" placement="top">
        <x-slot:triggerSlot>
            <x-ui.button
                type="button"
                size="icon"
                color="zinc"
                :loading="false"
                x-on:click="Livewire.dispatch('modal-open', { modal: {{ \Illuminate\Support\Js::from($viewModal) }}, props: { {{ $idProp }}: {{ $rowId }} } })"
            >
                <x-lucide-eye class="h-4 w-4" />
                <span class="sr-only">{{ __('general.view') }}</span>
            </x-ui.button>
        </x-slot:triggerSlot>
        {{ __('general.view') }}
    </x-fwb.tooltip>

    <x-fwb.tooltip :id="'pg-tooltip-edit-'.$rowId" placement="top">
        <x-slot:triggerSlot>
            <x-ui.button
                type="button"
                size="icon"
                color="blue"
                :loading="false"
                x-on:click="Livewire.dispatch('modal-open', { modal: {{ \Illuminate\Support\Js::from($editModal) }}, props: { {{ $idProp }}: {{ $rowId }} } })"
            >
                <x-lucide-pencil class="h-4 w-4" />
                <span class="sr-only">{{ __('general.edit') }}</span>
            </x-ui.button>
        </x-slot:triggerSlot>
        {{ __('general.edit') }}
    </x-fwb.tooltip>
</div>
