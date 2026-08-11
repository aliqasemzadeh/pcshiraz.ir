<?php

use App\Models\UserPaymentCard;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

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
    public function cards()
    {
        return UserPaymentCard::query()
            ->where('user_id', Auth::id())
            ->latest('id')
            ->paginate(config('main.per_page', 30));
    }

    #[On('shop.profile.card.saved')]
    #[On('shop.profile.card.deleted')]
    public function refreshCards(): void
    {
        unset($this->cards);
        $this->resetPage();
    }
};
?>

<x-shop.profile-shell :title="__('app.my_payment_cards')">
    <x-slot:actions>
        <x-ui.button
            type="button"
            color="green"
            :loading="false"
            x-modal:open="{ modal: 'user-payment-card.create' }"
        >
            <x-slot:icon>
                <x-lucide-plus class="me-2 h-4 w-4" />
            </x-slot:icon>
            {{ __('app.create_payment_card') }}
        </x-ui.button>
    </x-slot:actions>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        @if ($this->cards->isEmpty())
            <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600 dark:text-gray-400">
                {{ __('app.no_payment_cards') }}
            </div>
        @else
            <div class="space-y-4">
                @foreach ($this->cards as $card)
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700" wire:key="user-payment-card-{{ $card->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $card->title }}</h2>
                                    @if ($card->is_default)
                                        <span class="inline-flex items-center rounded bg-brand/10 px-2 py-0.5 text-xs font-medium text-brand">
                                            {{ __('app.default_payment_card') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-700 dark:text-gray-200">{{ $card->holder_name }}</p>
                                <p class="font-mono text-sm text-gray-600 dark:text-gray-300" dir="ltr">
                                    {{ $card->masked_card_number }}
                                </p>
                                @if ($card->bank_name)
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $card->bank_name }}</p>
                                @endif
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <x-ui.button
                                    type="button"
                                    size="icon"
                                    color="blue"
                                    :loading="false"
                                    x-on:click="Livewire.dispatch('modal-open', { modal: 'user-payment-card.edit', props: { cardId: {{ $card->id }} } })"
                                    :title="__('general.edit')"
                                >
                                    <x-lucide-pencil class="h-4 w-4" />
                                </x-ui.button>
                                <x-ui.button
                                    type="button"
                                    size="icon"
                                    color="red"
                                    :loading="false"
                                    x-on:click="Livewire.dispatch('modal-open', { modal: 'user-payment-card.delete', props: { cardId: {{ $card->id }} } })"
                                    :title="__('general.delete')"
                                >
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $this->cards->links() }}
            </div>
        @endif
    </div>
</x-shop.profile-shell>
