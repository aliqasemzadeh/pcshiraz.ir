<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Form;
use Morilog\Jalali\Jalalian;
use Throwable;

class ProfileForm extends Form
{
    public ?User $user = null;

    public string $mobile = '';

    public ?string $first_name = null;

    public ?string $last_name = null;

    public ?string $national_code = null;

    public ?string $birth_date = null;

    public ?string $email = null;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $request_identity_verification = false;

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
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->request_identity_verification = false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $locked = $this->user?->isIdentityVerified() ?? false;

        $rules = [];

        if (filled($this->password)) {
            $rules['password'] = ['required', 'string', 'confirmed', Password::min(8)];
            $rules['password_confirmation'] = ['required', 'string'];
        } else {
            $rules['password'] = ['nullable', 'string'];
            $rules['password_confirmation'] = ['nullable', 'string'];
        }

        if ($locked) {
            return $rules;
        }

        return array_merge($rules, [
            'mobile' => [
                'required',
                'ir_mobile:zero',
                Rule::unique('users', 'mobile')->whereNull('deleted_at')->ignore($this->user?->id),
            ],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'national_code' => ['nullable', 'ir_national_id', 'max:10'],
            'birth_date' => ['nullable', 'string', 'regex:/^\d{4}\/\d{1,2}\/\d{1,2}$/'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($this->user?->id),
            ],
            'request_identity_verification' => ['boolean'],
        ]);
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
            'email' => __('general.email'),
            'password' => __('general.password'),
            'password_confirmation' => __('general.password_confirmation'),
            'request_identity_verification' => __('general.identity_verification'),
        ];
    }

    public function update(User $user): User
    {
        $this->user = $user;
        $this->normalizeNullableFields();
        $this->validate();

        $locked = $user->isIdentityVerified();

        $data = [];

        if (! $locked) {
            $data = [
                'mobile' => $this->mobile,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'national_code' => $this->national_code,
                'birth_date' => $this->gregorianBirthDate(),
                'email' => $this->email,
            ];
        }

        if (filled($this->password)) {
            $data['password'] = $this->password;
        }

        if ($data !== []) {
            $user->update($data);
        }

        if (! $locked && $this->request_identity_verification) {
            $this->markIdentityVerified($user->fresh());
        }

        $this->password = '';
        $this->password_confirmation = '';
        $this->request_identity_verification = false;

        return $user->refresh();
    }

    public function markIdentityVerified(User $user): User
    {
        $this->user = $user;

        if ($user->isIdentityVerified()) {
            return $user;
        }

        $this->normalizeNullableFields();

        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'national_code' => ['required', 'ir_national_id', 'max:10'],
            'birth_date' => ['required', 'string', 'regex:/^\d{4}\/\d{1,2}\/\d{1,2}$/'],
            'mobile' => [
                'required',
                'ir_mobile:zero',
                Rule::unique('users', 'mobile')->whereNull('deleted_at')->ignore($user->id),
            ],
        ]);

        // Inquiry API will be wired here later; for now only persist verification flag.
        $user->forceFill([
            'mobile' => $this->mobile,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'national_code' => $this->national_code,
            'birth_date' => $this->gregorianBirthDate(),
            'identity_verified_at' => now(),
        ])->save();

        return $user->refresh();
    }

    protected function normalizeNullableFields(): void
    {
        foreach (['first_name', 'last_name', 'national_code', 'birth_date', 'email'] as $field) {
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
