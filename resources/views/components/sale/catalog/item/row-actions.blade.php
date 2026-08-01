@props([
    'row',
])

@php
    $rowId = $row->getKey();
@endphp

<div class="flex items-center gap-2">
    <x-fwb.tooltip :id="'pg-tooltip-price-'.$rowId" placement="top">
        <x-slot:triggerSlot>
            <a
                href="{{ route('panels.sale.catalog.item.price.index', $row) }}"
                wire:navigate
                class="inline-flex items-center justify-center rounded-base border border-transparent bg-teal-700 p-2 text-white shadow-xs hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-300 dark:bg-teal-600 dark:hover:bg-teal-700"
            >
                <x-lucide-banknote class="h-4 w-4" />
                <span class="sr-only">{{ __('general.pricing') }}</span>
            </a>
        </x-slot:triggerSlot>
        {{ __('general.pricing') }}
    </x-fwb.tooltip>

    <x-fwb.tooltip :id="'pg-tooltip-edit-'.$rowId" placement="top">
        <x-slot:triggerSlot>
            <x-ui.button
                type="button"
                size="icon"
                color="blue"
                :loading="false"
                x-on:click="Livewire.dispatch('modal-open', { modal: 'sale.catalog.item.edit', props: { itemId: {{ $rowId }} } })"
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
                x-on:click="Livewire.dispatch('modal-open', { modal: 'sale.catalog.item.delete', props: { itemId: {{ $rowId }} } })"
            >
                <x-lucide-trash-2 class="h-4 w-4" />
                <span class="sr-only">{{ __('general.delete') }}</span>
            </x-ui.button>
        </x-slot:triggerSlot>
        {{ __('general.delete') }}
    </x-fwb.tooltip>
</div>
