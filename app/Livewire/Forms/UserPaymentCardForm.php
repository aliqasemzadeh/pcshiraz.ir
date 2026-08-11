<?php

namespace App\Livewire\Forms;

use App\Models\User;
use App\Models\UserPaymentCard;
use Livewire\Form;

class UserPaymentCardForm extends Form
{
    public ?UserPaymentCard $paymentCard = null;

    public string $title = '';

    public string $holder_name = '';

    public string $card_number = '';

    public string $bank_name = '';

    public bool $is_default = false;

    public function setPaymentCard(UserPaymentCard $paymentCard): void
    {
        $this->paymentCard = $paymentCard;
        $this->title = $paymentCard->title;
        $this->holder_name = $paymentCard->holder_name;
        $this->card_number = $paymentCard->card_number;
        $this->bank_name = $paymentCard->bank_name ?? '';
        $this->is_default = $paymentCard->is_default;
    }

    public function resetForCreate(): void
    {
        $this->reset();
        $this->paymentCard = null;
        $this->is_default = false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'holder_name' => ['required', 'string', 'max:255'],
            'card_number' => ['required', 'string', 'regex:/^\d{16}$/'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'title' => __('app.payment_card_title'),
            'holder_name' => __('app.card_holder_name'),
            'card_number' => __('app.card_number'),
            'bank_name' => __('app.bank_name'),
            'is_default' => __('app.default_payment_card'),
        ];
    }

    public function store(User $user): UserPaymentCard
    {
        $this->normalizeCardNumber();
        $this->validate();

        if ($this->is_default) {
            $user->paymentCards()->update(['is_default' => false]);
        }

        return $user->paymentCards()->create([
            'title' => $this->title,
            'holder_name' => $this->holder_name,
            'card_number' => $this->card_number,
            'bank_name' => $this->bank_name !== '' ? $this->bank_name : null,
            'is_default' => $this->is_default,
        ]);
    }

    public function update(UserPaymentCard $paymentCard): UserPaymentCard
    {
        $this->paymentCard = $paymentCard;
        $this->normalizeCardNumber();
        $this->validate();

        if ($this->is_default) {
            $paymentCard->user->paymentCards()
                ->whereKeyNot($paymentCard->id)
                ->update(['is_default' => false]);
        }

        $paymentCard->update([
            'title' => $this->title,
            'holder_name' => $this->holder_name,
            'card_number' => $this->card_number,
            'bank_name' => $this->bank_name !== '' ? $this->bank_name : null,
            'is_default' => $this->is_default,
        ]);

        return $paymentCard->refresh();
    }

    private function normalizeCardNumber(): void
    {
        $this->card_number = preg_replace('/\D+/', '', $this->card_number) ?? '';
    }
}
