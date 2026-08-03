<?php

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\Sale\OrderApprovalService;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $orderId;

    public Order $order;

    /** @var array<int, int> */
    public array $quantities = [];

    public function mount(int $orderId): void
    {
        $this->orderId = $orderId;
        $this->loadOrder();
    }

    public function save(OrderApprovalService $service): void
    {
        $items = [];
        foreach ($this->quantities as $id => $quantity) {
            $items[] = ['id' => (int) $id, 'quantity' => (int) $quantity];
        }

        $service->updateItems($this->order, $items);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleOrdersTable');
    }

    public function markShipped(OrderApprovalService $service): void
    {
        $service->markShipped($this->order);

        Toaster::success(__('general.order_shipped'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleOrdersTable');
    }

    protected function loadOrder(): void
    {
        $this->order = Order::query()
            ->with(['items', 'installments', 'organization'])
            ->findOrFail($this->orderId);

        $this->quantities = $this->order->items
            ->mapWithKeys(fn ($item) => [$item->id => $item->quantity])
            ->all();
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-xl overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-2 text-xl font-semibold text-heading">
            {{ __('general.edit_order') }}
        </h2>
        <p class="mb-4 text-sm text-body" dir="ltr">{{ $order->order_number }}</p>

        <form wire:submit="save" class="space-y-4">
            <div class="space-y-3">
                @foreach ($order->items as $item)
                    <div class="rounded-lg border border-default p-3">
                        <div class="mb-2 font-medium text-heading">{{ $item->title }}</div>
                        <x-fwb.input
                            type="number"
                            min="0"
                            wire:model="quantities.{{ $item->id }}"
                            :label="__('general.quantity')"
                        />
                    </div>
                @endforeach
            </div>

            <x-ui.button type="submit" color="orange" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>

            @if (in_array($order->status, [
                OrderStatusEnum::Approved,
                OrderStatusEnum::AwaitingDownPayment,
                OrderStatusEnum::InstallmentActive,
                OrderStatusEnum::Processing,
            ], true))
                <x-ui.button type="button" color="teal" target="markShipped" class="w-full" wire:click="markShipped" wire:confirm="{{ __('general.are_you_sure') }}">
                    {{ __('general.mark_as_shipped') }}
                </x-ui.button>
            @endif
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
