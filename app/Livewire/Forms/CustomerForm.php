<?php

namespace App\Livewire\Forms;

use App\Models\Customer;
use Illuminate\Validation\ValidationException;
use Livewire\Form;
use Morilog\Jalali\Jalalian;
use Throwable;

class CustomerForm extends Form
{
    public ?Customer $customer = null;

    public ?int $user_id = null;

    public ?int $domain_id = null;

    public ?string $first_name = null;

    public ?string $last_name = null;

    public ?string $national_code = null;

    public ?string $birth_date = null;

    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
        $this->user_id = $customer->user_id;
        $this->domain_id = $customer->domain_id;
        $this->first_name = $customer->first_name;
        $this->last_name = $customer->last_name;
        $this->national_code = $customer->national_code;
        $this->birth_date = $customer->birth_date
            ? Jalalian::fromDateTime($customer->birth_date)->format('Y/m/d')
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'domain_id' => ['required', 'integer', 'exists:domains,id'],
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
            'user_id' => __('general.user'),
            'domain_id' => __('general.domains'),
            'first_name' => __('general.first_name'),
            'last_name' => __('general.last_name'),
            'national_code' => __('general.national_code'),
            'birth_date' => __('general.birth_date'),
        ];
    }

    public function store(): Customer
    {
        $this->validate();

        return Customer::query()->create([
            'user_id' => $this->user_id,
            'domain_id' => $this->domain_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'national_code' => $this->national_code,
            'birth_date' => $this->gregorianBirthDate(),
        ]);
    }

    public function update(Customer $customer): Customer
    {
        $this->customer = $customer;
        $this->validate();

        $customer->update([
            'user_id' => $this->user_id,
            'domain_id' => $this->domain_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'national_code' => $this->national_code,
            'birth_date' => $this->gregorianBirthDate(),
        ]);

        return $customer->refresh();
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
