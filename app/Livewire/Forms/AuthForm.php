<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AuthForm extends Form
{
    public ?User $user = null;

    #[Validate('required|ir_mobile:zero', as: 'main.mobile')]
    public string $mobile = '';

    public string $code = '';

    public function getUser(): User
    {
        if ($this->user) {
            return $this->user;
        }

        return $this->user = User::firstOrCreate(['mobile' => $this->mobile]);
    }
}
