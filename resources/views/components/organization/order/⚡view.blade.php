<?php

use App\Models\Order;
use Livewire\Component;

new class extends Component
{
    public int $orderId;

    public Order $order;

    public function mount(int $orderId): void
    {
        $this->orderId = $orderId;
        $this->order = Order::query()
            ->with(['items', 'installments', 'organization', 'user'])
            ->findOrFail($orderId);
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-2xl overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.order_details') }}
        </h2>

        @include('components.sale.order.partials.order-details', ['order' => $order])
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
