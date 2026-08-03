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

    public string $rejection_note = '';

    public function mount(int $orderId): void
    {
        $order = Order::query()->findOrFail($orderId);
        $this->orderId = $orderId;
        $this->orderNumber = $order->order_number;
    }

    public function reject(OrderApprovalService $service): void
    {
        $this->validate([
            'rejection_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Order::query()->findOrFail($this->orderId);
        $service->reject($order, Auth::user(), $this->rejection_note ?: null);

        Toaster::success(__('general.order_rejected'));
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
            {{ __('general.reject_order') }}
        </h3>
        <p class="mb-4 text-sm text-body" dir="ltr">{{ $orderNumber }}</p>

        <form wire:submit="reject" class="space-y-4">
            <div>
                <x-fwb.textarea
                    wire:model="rejection_note"
                    :label="__('general.rejection_note')"
                    rows="3"
                />
            </div>

            <div class="flex justify-end gap-2">
                <x-ui.button type="button" color="light" outline :loading="false" x-modal:close>
                    {{ __('general.cancel') }}
                </x-ui.button>
                <x-ui.button type="submit" color="red" target="reject">
                    {{ __('general.reject') }}
                </x-ui.button>
            </div>
        </form>
    </x-livewire-modal::modal>
</x-livewire-modal::stack>
