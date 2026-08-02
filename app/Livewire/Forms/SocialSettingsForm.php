<?php

namespace App\Livewire\Forms;

use App\Settings\SocialSettings;
use Livewire\Form;

class SocialSettingsForm extends Form
{
    public string $telegram = '';

    public string $eitaa = '';

    public string $bale = '';

    public string $rubika = '';

    public string $soroush = '';

    public string $aparat = '';

    public string $instagram = '';

    public string $linkedin = '';

    public string $x = '';

    public function fillFromSettings(SocialSettings $settings): void
    {
        $this->telegram = $settings->telegram;
        $this->eitaa = $settings->eitaa;
        $this->bale = $settings->bale;
        $this->rubika = $settings->rubika;
        $this->soroush = $settings->soroush;
        $this->aparat = $settings->aparat;
        $this->instagram = $settings->instagram;
        $this->linkedin = $settings->linkedin;
        $this->x = $settings->x;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'telegram' => ['nullable', 'url', 'max:255'],
            'eitaa' => ['nullable', 'url', 'max:255'],
            'bale' => ['nullable', 'url', 'max:255'],
            'rubika' => ['nullable', 'url', 'max:255'],
            'soroush' => ['nullable', 'url', 'max:255'],
            'aparat' => ['nullable', 'url', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'x' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'telegram' => __('app.social_telegram'),
            'eitaa' => __('app.social_eitaa'),
            'bale' => __('app.social_bale'),
            'rubika' => __('app.social_rubika'),
            'soroush' => __('app.social_soroush'),
            'aparat' => __('app.social_aparat'),
            'instagram' => __('app.social_instagram'),
            'linkedin' => __('app.social_linkedin'),
            'x' => __('app.social_x'),
        ];
    }

    public function save(SocialSettings $settings): void
    {
        $this->validate();

        $settings->telegram = $this->telegram ?? '';
        $settings->eitaa = $this->eitaa ?? '';
        $settings->bale = $this->bale ?? '';
        $settings->rubika = $this->rubika ?? '';
        $settings->soroush = $this->soroush ?? '';
        $settings->aparat = $this->aparat ?? '';
        $settings->instagram = $this->instagram ?? '';
        $settings->linkedin = $this->linkedin ?? '';
        $settings->x = $this->x ?? '';
        $settings->save();
    }
}
