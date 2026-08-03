<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        if (! Auth::check() || $order->user_id !== Auth::id()) {
            abort(403);
        }

        $this->order = $order->load(['organization', 'installments']);
    }
};
?>

<x-slot name="title">{{ __('general.order_submitted') }} - {{ config('app.name') }}</x-slot>

<div class="mx-auto max-w-lg space-y-4 px-4 py-12 text-center">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
        <x-lucide-check class="h-8 w-8" />
    </div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('general.order_submitted') }}</h1>
    <p class="text-gray-600 dark:text-gray-300">{{ __('general.order_pending_approval_message') }}</p>
    <p class="font-mono text-lg text-gray-900 dark:text-white" dir="ltr">{{ $order->order_number }}</p>
    <a href="{{ route('home') }}" wire:navigate class="inline-flex text-brand hover:underline">
        {{ __('general.home') }}
    </a>
</div>
