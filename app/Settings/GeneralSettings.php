<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;

    public string $site_short_name;

    public string $site_description;

    /** @var array<int, string> */
    public array $site_tags;

    public string $locale;

    public string $timezone;

    public ?string $logo_path;

    public ?string $favicon_path;

    public static function group(): string
    {
        return 'general';
    }
}
