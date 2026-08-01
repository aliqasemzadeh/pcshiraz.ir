<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AuthForm extends Form
{
    public ?User $user = null;

    #[Validate('required|ir_mobile:zero', as: 'general.mobile')]
    public string $mobile = '';

    public string $code = '';

    public function getUser(): User
    {
        if ($this->user) {
            return $this->user;
        }

        $user = User::withTrashed()->firstOrCreate(
            ['mobile' => $this->mobile],
        );

        if ($user->trashed()) {
            $user->restore();
        }

        return $this->user = $user;
    }
}
