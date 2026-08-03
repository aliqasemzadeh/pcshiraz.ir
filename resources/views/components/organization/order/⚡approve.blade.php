<?php

use App\Models\Order;
use App\Services\Sale\OrderApprovalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $orderId;

    public string $orderNumber = '';

    public function mount(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);
        $this->orderId = $orderId;
        $this->orderNumber = $order->order_number;
    }

    public function approve(OrderApprovalService $service): void
    {
        $order = Order::query()->findOrFail($this->orderId);
        $service->approve($order, Auth::user());

        Toaster::success(__('general.order_approved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-organizationOrdersTable');
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::modal
        position="center"
        class="w-full max-w-md overflow-auto rounded-lg bg-white p-5 dark:bg-gray-800"
    >
        <h3 class="mb-2 text-lg font-semibold text-heading">
            {{ __('general.approve_order') }}
        </h3>
        <p class="mb-2 text-sm text-body" dir="ltr">{{ $orderNumber }}</p>
        <p class="mb-6 text-sm text-body">{{ __('general.approve_order_confirm') }}</p>
        <div class="flex justify-end gap-2">
            <x-ui.button type="button" color="light" outline :loading="false" x-modal:close>
                {{ __('general.cancel') }}
            </x-ui.button>
            <x-ui.button type="button" color="green" target="approve" wire:click="approve">
                {{ __('general.approve') }}
            </x-ui.button>
        </div>
    </x-livewire-modal::modal>
</x-livewire-modal::stack>
