<?php

use App\Livewire\Forms\UserPaymentCardForm;
use App\Models\UserPaymentCard;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public UserPaymentCardForm $form;

    public int $cardId;

    public function mount(int $cardId): void
    {
        $this->cardId = $cardId;

        $card = UserPaymentCard::query()
            ->where('user_id', Auth::id())
            ->findOrFail($cardId);

        $this->form->setPaymentCard($card);
    }

    public function save(): void
    {
        $card = UserPaymentCard::query()
            ->where('user_id', Auth::id())
            ->findOrFail($this->cardId);

        $this->form->update($card);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('shop.profile.card.saved');
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('app.edit_payment_card') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            @include('components.user-payment-card.partials.form-fields', [
                'form' => $form,
            ])

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
