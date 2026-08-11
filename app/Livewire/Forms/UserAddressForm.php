<?php

namespace App\Livewire\Forms;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UserAddressForm extends Form
{
    public ?UserAddress $userAddress = null;

    public string $title = '';

    public string $postal_code = '';

    public int|string|null $province_id = null;

    public int|string|null $city_id = null;

    public string $address = '';

    public function setUserAddress(UserAddress $userAddress): void
    {
        $this->userAddress = $userAddress;
        $this->title = $userAddress->title;
        $this->postal_code = $userAddress->postal_code;
        $this->province_id = $userAddress->province_id;
        $this->city_id = $userAddress->city_id;
        $this->address = $userAddress->address;
    }

    public function resetForCreate(): void
    {
        $this->reset();
        $this->userAddress = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'ir_postal_code'],
            'province_id' => ['required', 'integer', 'exists:provinces,id'],
            'city_id' => [
                'required',
                'integer',
                Rule::exists('cities', 'id')->where('province_id', $this->province_id),
            ],
            'address' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'title' => __('app.address_title'),
            'postal_code' => __('app.postal_code'),
            'province_id' => __('app.province'),
            'city_id' => __('app.city'),
            'address' => __('app.address_text'),
        ];
    }

    public function store(User $user): UserAddress
    {
        $this->validate();

        return $user->addresses()->create([
            'title' => $this->title,
            'postal_code' => $this->postal_code,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'address' => $this->address,
        ]);
    }

    public function update(UserAddress $userAddress): UserAddress
    {
        $this->userAddress = $userAddress;
        $this->validate();

        $userAddress->update([
            'title' => $this->title,
            'postal_code' => $this->postal_code,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'address' => $this->address,
        ]);

        return $userAddress->refresh();
    }
}
