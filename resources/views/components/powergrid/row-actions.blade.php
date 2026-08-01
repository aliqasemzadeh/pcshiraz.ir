{{--
  Shared PowerGrid row actions with Flowbite tooltips (x-fwb.tooltip).
  Required props: $row, $editModal, $deleteModal, $idProp
--}}
@props([
    'row',
    'editModal',
    'deleteModal',
    'idProp',
])

@php
    $rowId = $row->getKey();
@endphp

<div class="flex items-center gap-2">
    <x-fwb.tooltip :id="'pg-tooltip-edit-'.$rowId" placement="top">
        <x-slot:triggerSlot>
            <x-ui.button
                type="button"
                size="icon"
                color="blue"
                :loading="false"
                wire:click="$dispatch('modal-open', { modal: @js($editModal), props: { {{ $idProp }}: {{ $rowId }} } })"
            >
                <x-lucide-pencil class="h-4 w-4" />
                <span class="sr-only">{{ __('general.edit') }}</span>
            </x-ui.button>
        </x-slot:triggerSlot>
        {{ __('general.edit') }}
    </x-fwb.tooltip>

    <x-fwb.tooltip :id="'pg-tooltip-delete-'.$rowId" placement="top">
        <x-slot:triggerSlot>
            <x-ui.button
                type="button"
                size="icon"
                color="red"
                :loading="false"
                wire:click="$dispatch('modal-open', { modal: @js($deleteModal), props: { {{ $idProp }}: {{ $rowId }} } })"
            >
                <x-lucide-trash-2 class="h-4 w-4" />
                <span class="sr-only">{{ __('general.delete') }}</span>
            </x-ui.button>
        </x-slot:triggerSlot>
        {{ __('general.delete') }}
    </x-fwb.tooltip>
</div>
