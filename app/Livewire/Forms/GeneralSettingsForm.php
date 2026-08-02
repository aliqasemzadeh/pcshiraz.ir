<?php

namespace App\Livewire\Forms;

use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class GeneralSettingsForm extends Form
{
    public string $site_name = '';

    public string $site_short_name = '';

    public string $site_description = '';

    /** @var array<int, string> */
    public array $site_tags = [];

    public string $tag_input = '';

    public string $locale = 'fa';

    public string $timezone = 'Asia/Tehran';

    public TemporaryUploadedFile|string|null $logo = null;

    public TemporaryUploadedFile|string|null $favicon = null;

    public ?string $logo_path = null;

    public ?string $favicon_path = null;

    public function fillFromSettings(GeneralSettings $settings): void
    {
        $this->site_name = $settings->site_name;
        $this->site_short_name = $settings->site_short_name;
        $this->site_description = $settings->site_description;
        $this->site_tags = $settings->site_tags;
        $this->locale = $settings->locale;
        $this->timezone = $settings->timezone;
        $this->logo_path = $settings->logo_path;
        $this->favicon_path = $settings->favicon_path;
        $this->logo = null;
        $this->favicon = null;
        $this->tag_input = '';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:255'],
            'site_short_name' => ['required', 'string', 'min:2', 'max:4'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'site_tags' => ['nullable', 'array'],
            'site_tags.*' => ['string', 'max:50'],
            'locale' => ['required', 'string', Rule::in(['fa', 'en'])],
            'timezone' => ['required', 'timezone'],
            'logo' => [
                'nullable',
                'file',
                'max:2048',
                'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml',
            ],
            'favicon' => [
                'nullable',
                'file',
                'max:1024',
                'mimetypes:image/png,image/x-icon,image/vnd.microsoft.icon,image/jpeg,image/webp',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'site_name' => __('app.site_name'),
            'site_short_name' => __('app.site_short_name'),
            'site_description' => __('app.site_description'),
            'site_tags' => __('app.site_tags'),
            'locale' => __('app.locale'),
            'timezone' => __('app.timezone'),
            'logo' => __('app.site_logo'),
            'favicon' => __('app.favicon'),
        ];
    }

    public function addTag(): void
    {
        $tag = trim($this->tag_input);

        if ($tag === '') {
            return;
        }

        if (! in_array($tag, $this->site_tags, true)) {
            $this->site_tags[] = $tag;
        }

        $this->tag_input = '';
    }

    public function removeTag(int $index): void
    {
        unset($this->site_tags[$index]);
        $this->site_tags = array_values($this->site_tags);
    }

    public function save(GeneralSettings $settings): void
    {
        $this->addTag();
        $this->validate();

        $settings->site_name = $this->site_name;
        $settings->site_short_name = $this->site_short_name;
        $settings->site_description = $this->site_description ?? '';
        $settings->site_tags = array_values($this->site_tags);
        $settings->locale = $this->locale;
        $settings->timezone = $this->timezone;

        if ($this->logo instanceof TemporaryUploadedFile) {
            $this->deleteStoredFile($settings->logo_path);
            $settings->logo_path = $this->logo->store('settings', 'public');
            $this->logo_path = $settings->logo_path;
            $this->logo = null;
        }

        if ($this->favicon instanceof TemporaryUploadedFile) {
            $this->deleteStoredFile($settings->favicon_path);
            $settings->favicon_path = $this->favicon->store('settings', 'public');
            $this->favicon_path = $settings->favicon_path;
            $this->favicon = null;
        }

        $settings->save();
    }

    protected function deleteStoredFile(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
