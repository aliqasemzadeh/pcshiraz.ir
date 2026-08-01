<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;
use Morilog\Jalali\Jalalian;
use Throwable;

class UserForm extends Form
{
    public ?User $user = null;

    public string $mobile = '';

    public ?string $first_name = null;

    public ?string $last_name = null;

    public ?string $national_code = null;

    public ?string $birth_date = null;

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->mobile = $user->mobile;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->national_code = $user->national_code;
        $this->birth_date = $user->birth_date
            ? Jalalian::fromDateTime($user->birth_date)->format('Y/m/d')
            : null;
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
                Rule::unique('users', 'mobile')->whereNull('deleted_at')->ignore($this->user?->id),
            ],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'national_code' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'string', 'regex:/^\d{4}\/\d{1,2}\/\d{1,2}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'mobile' => __('general.mobile'),
            'first_name' => __('general.first_name'),
            'last_name' => __('general.last_name'),
            'national_code' => __('general.national_code'),
            'birth_date' => __('general.birth_date'),
        ];
    }

    public function store(): User
    {
        $this->normalizeNullableFields();
        $this->validate();

        return User::query()->create([
            'mobile' => $this->mobile,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'national_code' => $this->national_code,
            'birth_date' => $this->gregorianBirthDate(),
        ]);
    }

    public function update(User $user): User
    {
        $this->user = $user;
        $this->normalizeNullableFields();
        $this->validate();

        $user->update([
            'mobile' => $this->mobile,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'national_code' => $this->national_code,
            'birth_date' => $this->gregorianBirthDate(),
        ]);

        return $user->refresh();
    }

    protected function normalizeNullableFields(): void
    {
        foreach (['first_name', 'last_name', 'national_code', 'birth_date'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }
    }

    protected function gregorianBirthDate(): ?string
    {
        if ($this->birth_date === null || trim($this->birth_date) === '') {
            return null;
        }

        try {
            return Jalalian::fromFormat('Y/m/d', $this->birth_date)->toCarbon()->toDateString();
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'birth_date' => __('validation.date', ['attribute' => __('general.birth_date')]),
            ]);
        }
    }
}
