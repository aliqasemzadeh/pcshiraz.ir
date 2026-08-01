<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user = null;

    public string $mobile = '';

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->mobile = $user->mobile;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mobile' => [
                'required',
                'ir_mobile:zero',
                Rule::unique('users', 'mobile')->ignore($this->user?->id),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'mobile' => __('general.mobile'),
        ];
    }

    public function store(): User
    {
        $this->validate();

        return User::query()->create([
            'mobile' => $this->mobile,
        ]);
    }

    public function update(User $user): User
    {
        $this->user = $user;
        $this->validate();

        $user->update([
            'mobile' => $this->mobile,
        ]);

        return $user->refresh();
    }
}
