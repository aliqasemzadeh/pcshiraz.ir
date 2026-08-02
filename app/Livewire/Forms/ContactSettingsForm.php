<?php

namespace App\Livewire\Forms;

use App\Settings\ContactSettings;
use Livewire\Form;

class ContactSettingsForm extends Form
{
    public string $address = '';

    public string $postal_code = '';

    public string $fax = '';

    public string $support_email = '';

    /** @var array<int, string> */
    public array $phones = [];

    public function fillFromSettings(ContactSettings $settings): void
    {
        $this->address = $settings->address;
        $this->postal_code = $settings->postal_code;
        $this->fax = $settings->fax;
        $this->support_email = $settings->support_email;
        $this->phones = $settings->phones !== [] ? $settings->phones : [''];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'address' => ['nullable', 'string', 'max:1000'],
            'postal_code' => ['nullable', 'string', 'digits:10'],
            'fax' => ['nullable', 'string', 'max:30'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'phones' => ['nullable', 'array'],
            'phones.*' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'address' => __('app.address'),
            'postal_code' => __('app.postal_code'),
            'fax' => __('app.fax'),
            'support_email' => __('app.support_email'),
            'phones' => __('app.phones'),
            'phones.*' => __('app.phone'),
        ];
    }

    public function addPhone(): void
    {
        $this->phones[] = '';
    }

    public function removePhone(int $index): void
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);

        if ($this->phones === []) {
            $this->phones = [''];
        }
    }

    public function save(ContactSettings $settings): void
    {
        $this->validate();

        $settings->address = $this->address ?? '';
        $settings->postal_code = $this->postal_code ?? '';
        $settings->fax = $this->fax ?? '';
        $settings->support_email = $this->support_email ?? '';
        $settings->phones = array_values(array_filter(
            array_map(static fn (string $phone): string => trim($phone), $this->phones),
            static fn (string $phone): bool => $phone !== ''
        ));
        $settings->save();

        $this->phones = $settings->phones !== [] ? $settings->phones : [''];
    }
}
