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
            <button
                type="button"
                wire:click="$dispatch('modal-open', { modal: @js($editModal), props: { {{ $idProp }}: {{ $rowId }} } })"
                class="inline-flex items-center justify-center p-2 text-white bg-brand border border-transparent rounded-base shadow-xs hover:bg-brand-strong focus:outline-none focus:ring-4 focus:ring-brand-medium"
            >
                <x-lucide-pencil class="h-4 w-4" />
                <span class="sr-only">{{ __('general.edit') }}</span>
            </button>
        </x-slot:triggerSlot>
        {{ __('general.edit') }}
    </x-fwb.tooltip>

    <x-fwb.tooltip :id="'pg-tooltip-delete-'.$rowId" placement="top">
        <x-slot:triggerSlot>
            <button
                type="button"
                wire:click="$dispatch('modal-open', { modal: @js($deleteModal), props: { {{ $idProp }}: {{ $rowId }} } })"
                class="inline-flex items-center justify-center p-2 text-white bg-danger border border-transparent rounded-base shadow-xs hover:bg-danger-strong focus:outline-none focus:ring-4 focus:ring-danger-medium"
            >
                <x-lucide-trash-2 class="h-4 w-4" />
                <span class="sr-only">{{ __('general.delete') }}</span>
            </button>
        </x-slot:triggerSlot>
        {{ __('general.delete') }}
    </x-fwb.tooltip>
</div>
