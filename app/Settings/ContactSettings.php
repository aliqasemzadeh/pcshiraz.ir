<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public string $address;

    public string $postal_code;

    public string $fax;

    public string $support_email;

    /** @var array<int, string> */
    public array $phones;

    public static function group(): string
    {
        return 'contact';
    }
}
