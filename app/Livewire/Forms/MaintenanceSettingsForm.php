<?php

namespace App\Livewire\Forms;

use App\Settings\MaintenanceSettings;
use Livewire\Form;

class MaintenanceSettingsForm extends Form
{
    public bool $enabled = false;

    public string $secret = '';

    public string $message = '';

    public function fillFromSettings(MaintenanceSettings $settings): void
    {
        $this->enabled = $settings->enabled;
        $this->secret = $settings->secret;
        $this->message = $settings->message;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['boolean'],
            'secret' => ['required', 'string', 'min:6', 'max:64', 'alpha_dash:ascii'],
            'message' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'enabled' => __('app.maintenance_mode'),
            'secret' => __('app.maintenance_secret'),
            'message' => __('app.maintenance_message'),
        ];
    }

    public function save(MaintenanceSettings $settings): void
    {
        $this->validate();

        $settings->enabled = $this->enabled;
        $settings->secret = $this->secret;
        $settings->message = $this->message;
        $settings->save();
    }
}
