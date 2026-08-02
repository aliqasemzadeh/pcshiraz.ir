{{--
  Shared PowerGrid row actions with Flowbite tooltips (x-fwb.tooltip).
  Required props: $row, $editModal, $deleteModal, $idProp
  Optional: $extraModals — array of ['modal','icon','label','color']
--}}
@props([
    'row',
    'editModal',
    'deleteModal',
    'idProp',
    'extraModals' => [],
])

@php
    $rowId = $row->getKey();
@endphp

<div class="flex items-center gap-2">
    @foreach ($extraModals as $index => $extra)
        <x-fwb.tooltip :id="'pg-tooltip-extra-'.$index.'-'.$rowId" placement="top">
            <x-slot:triggerSlot>
                <x-ui.button
                    type="button"
                    size="icon"
                    :color="$extra['color'] ?? 'zinc'"
                    :loading="false"
                    x-on:click="Livewire.dispatch('modal-open', { modal: {{ \Illuminate\Support\Js::from($extra['modal']) }}, props: { {{ $idProp }}: {{ $rowId }} } })"
                >
                    @switch($extra['icon'] ?? 'circle')
                        @case('shield')
                            <x-lucide-shield class="h-4 w-4" />
                            @break
                        @case('key-round')
                            <x-lucide-key-round class="h-4 w-4" />
                            @break
                        @case('users')
                            <x-lucide-users class="h-4 w-4" />
                            @break
                        @default
                            <x-lucide-circle class="h-4 w-4" />
                    @endswitch
                    <span class="sr-only">{{ $extra['label'] }}</span>
                </x-ui.button>
            </x-slot:triggerSlot>
            {{ $extra['label'] }}
        </x-fwb.tooltip>
    @endforeach

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

    <x-fwb.tooltip :id="'pg-tooltip-delete-'.$rowId" placement="top">
        <x-slot:triggerSlot>
            <x-ui.button
                type="button"
                size="icon"
                color="red"
                :loading="false"
                x-on:click="Livewire.dispatch('modal-open', { modal: {{ \Illuminate\Support\Js::from($deleteModal) }}, props: { {{ $idProp }}: {{ $rowId }} } })"
            >
                <x-lucide-trash-2 class="h-4 w-4" />
                <span class="sr-only">{{ __('general.delete') }}</span>
            </x-ui.button>
        </x-slot:triggerSlot>
        {{ __('general.delete') }}
    </x-fwb.tooltip>
</div>
