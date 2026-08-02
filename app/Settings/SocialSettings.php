<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SocialSettings extends Settings
{
    public string $telegram;

    public string $eitaa;

    public string $bale;

    public string $rubika;

    public string $soroush;

    public string $aparat;

    public string $instagram;

    public string $linkedin;

    public string $x;

    public static function group(): string
    {
        return 'social';
    }
}
