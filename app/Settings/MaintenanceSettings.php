<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MaintenanceSettings extends Settings
{
    public bool $enabled;

    public string $secret;

    public string $message;

    public static function group(): string
    {
        return 'maintenance';
    }
}
