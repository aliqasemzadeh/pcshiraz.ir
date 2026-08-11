<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

new #[Layout('layouts.app')] class extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $this->order = $order->load(['items', 'organization', 'installments', 'installmentPlan']);
    }
};
?>

<x-shop.profile-shell :title="__('app.order_details')">
    <div class="mb-4">
        <a
            href="{{ route('profile.orders') }}"
            wire:navigate
            class="inline-flex items-center gap-1 text-sm text-brand hover:underline"
        >
            <x-lucide-arrow-right class="h-4 w-4 rtl:rotate-180" />
            {{ __('app.back_to_orders') }}
        </a>
    </div>

    <div class="space-y-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 pb-4 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white" dir="ltr">{{ $order->order_number }}</h2>
            <span class="inline-flex items-center rounded bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                {{ $order->status->label() }}
            </span>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ Jalalian::fromDateTime($order->submitted_at ?? $order->created_at)->format('Y/m/d H:i') }}
            </span>
        </div>

        @include('components.sale.order.partials.order-details', ['order' => $order])
    </div>
</x-shop.profile-shell>
