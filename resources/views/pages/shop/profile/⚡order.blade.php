<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login', navigate: true);
        }
    }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->where('user_id', Auth::id())
            ->with(['items', 'organization'])
            ->withCount('items')
            ->latest('id')
            ->paginate(config('main.per_page', 30));
    }
};
?>

<x-shop.profile-shell :title="__('app.my_orders')">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        @if ($this->orders->isEmpty())
            <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600 dark:text-gray-400">
                {{ __('app.no_orders') }}
            </div>
        @else
            <div class="space-y-4">
                @foreach ($this->orders as $order)
                    <a
                        href="{{ route('profile.orders.show', $order) }}"
                        wire:navigate
                        wire:key="user-order-{{ $order->id }}"
                        class="block rounded-lg border border-gray-200 p-4 transition hover:border-brand/40 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-900/40"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-semibold text-gray-900 dark:text-white" dir="ltr">
                                        {{ $order->order_number }}
                                    </h2>
                                    <span class="inline-flex items-center rounded bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        {{ $order->status->label() }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ Jalalian::fromDateTime($order->submitted_at ?? $order->created_at)->format('Y/m/d H:i') }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ __('general.order_items') }}: {{ $order->items_count }}
                                    @if ($order->organization)
                                        · {{ $order->organization->code }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-start sm:text-end">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('general.total_payable') }}</div>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ number_format((float) $order->total_payable) }}
                                    <span class="text-xs font-normal text-gray-500">{{ __('app.rial') }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $this->orders->links() }}
            </div>
        @endif
    </div>
</x-shop.profile-shell>
