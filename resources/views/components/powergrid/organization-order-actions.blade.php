@props([
    'row',
])

@php
    use App\Enums\OrderStatusEnum;
    $rowId = $row->getKey();
    $canDecide = $row->status === OrderStatusEnum::PendingApproval;
@endphp

<div class="flex items-center gap-2">
    <x-fwb.tooltip :id="'pg-org-view-'.$rowId" placement="top">
        <x-slot:triggerSlot>
            <x-ui.button
                type="button"
                size="icon"
                color="zinc"
                :loading="false"
                x-on:click="Livewire.dispatch('modal-open', { modal: 'organization.order.view', props: { orderId: {{ $rowId }} } })"
            >
                <x-lucide-eye class="h-4 w-4" />
                <span class="sr-only">{{ __('general.view') }}</span>
            </x-ui.button>
        </x-slot:triggerSlot>
        {{ __('general.view') }}
    </x-fwb.tooltip>

    @if ($canDecide)
        <x-fwb.tooltip :id="'pg-org-approve-'.$rowId" placement="top">
            <x-slot:triggerSlot>
                <x-ui.button
                    type="button"
                    size="icon"
                    color="green"
                    :loading="false"
                    x-on:click="Livewire.dispatch('modal-open', { modal: 'organization.order.approve', props: { orderId: {{ $rowId }} } })"
                >
                    <x-lucide-check class="h-4 w-4" />
                    <span class="sr-only">{{ __('general.approve') }}</span>
                </x-ui.button>
            </x-slot:triggerSlot>
            {{ __('general.approve') }}
        </x-fwb.tooltip>

        <x-fwb.tooltip :id="'pg-org-reject-'.$rowId" placement="top">
            <x-slot:triggerSlot>
                <x-ui.button
                    type="button"
                    size="icon"
                    color="red"
                    :loading="false"
                    x-on:click="Livewire.dispatch('modal-open', { modal: 'organization.order.reject', props: { orderId: {{ $rowId }} } })"
                >
                    <x-lucide-x class="h-4 w-4" />
                    <span class="sr-only">{{ __('general.reject') }}</span>
                </x-ui.button>
            </x-slot:triggerSlot>
            {{ __('general.reject') }}
        </x-fwb.tooltip>
    @endif
</div>
