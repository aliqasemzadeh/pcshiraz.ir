<?php

use App\Livewire\Forms\ContactSettingsForm;
use App\Livewire\Forms\GeneralSettingsForm;
use App\Livewire\Forms\MaintenanceSettingsForm;
use App\Livewire\Forms\SocialSettingsForm;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\MaintenanceSettings;
use App\Settings\SocialSettings;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

new #[Layout('layouts.panels')] class extends Component
{
    use WithFileUploads;

    public string $tab = 'general';

    public GeneralSettingsForm $generalForm;

    public MaintenanceSettingsForm $maintenanceForm;

    public ContactSettingsForm $contactForm;

    public SocialSettingsForm $socialForm;

    public function mount(
        GeneralSettings $generalSettings,
        MaintenanceSettings $maintenanceSettings,
        ContactSettings $contactSettings,
        SocialSettings $socialSettings,
    ): void {
        $this->generalForm->fillFromSettings($generalSettings);
        $this->maintenanceForm->fillFromSettings($maintenanceSettings);
        $this->contactForm->fillFromSettings($contactSettings);
        $this->socialForm->fillFromSettings($socialSettings);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['general', 'maintenance', 'contact', 'social'], true)) {
            return;
        }

        $this->tab = $tab;
    }

    public function addTag(): void
    {
        $this->generalForm->addTag();
    }

    public function removeTag(int $index): void
    {
        $this->generalForm->removeTag($index);
    }

    public function addPhone(): void
    {
        $this->contactForm->addPhone();
    }

    public function removePhone(int $index): void
    {
        $this->contactForm->removePhone($index);
    }

    public function saveGeneral(GeneralSettings $settings): void
    {
        $this->generalForm->save($settings);
        Toaster::success(__('general.saved'));
    }

    public function saveMaintenance(MaintenanceSettings $settings): void
    {
        $this->maintenanceForm->save($settings);
        Toaster::success(__('general.saved'));
    }

    public function saveContact(ContactSettings $settings): void
    {
        $this->contactForm->save($settings);
        Toaster::success(__('general.saved'));
    }

    public function saveSocial(SocialSettings $settings): void
    {
        $this->socialForm->save($settings);
        Toaster::success(__('general.saved'));
    }

    public function logoPreviewUrl(): ?string
    {
        if ($this->generalForm->logo) {
            return $this->generalForm->logo->temporaryUrl();
        }

        if ($this->generalForm->logo_path) {
            return Storage::disk('public')->url($this->generalForm->logo_path);
        }

        return null;
    }

    public function faviconPreviewUrl(): ?string
    {
        if ($this->generalForm->favicon) {
            return $this->generalForm->favicon->temporaryUrl();
        }

        if ($this->generalForm->favicon_path) {
            return Storage::disk('public')->url($this->generalForm->favicon_path);
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    public function timezones(): array
    {
        return [
            'Asia/Tehran' => 'Asia/Tehran',
            'UTC' => 'UTC',
            'Asia/Dubai' => 'Asia/Dubai',
            'Europe/London' => 'Europe/London',
            'Europe/Berlin' => 'Europe/Berlin',
            'America/New_York' => 'America/New_York',
        ];
    }
};
?>

<x-slot name="title">{{ __('general.settings') }} - {{ config('app.name') }}</x-slot>

<div>
    <x-fwb.breadcrumb class="mb-4">
        <x-fwb.breadcrumb.item home>{{ __('general.administrator') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item>{{ __('general.settings') }}</x-fwb.breadcrumb.item>
    </x-fwb.breadcrumb>

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-heading">{{ __('general.settings') }}</h1>
            <p class="mt-1 text-sm text-body">{{ __('app.settings_subtitle') }}</p>
        </div>

        <div class="flex flex-wrap gap-2 border-b border-default pb-3">
            @foreach ([
                'general' => __('app.general_settings'),
                'maintenance' => __('app.maintenance_settings'),
                'contact' => __('app.contact_settings'),
                'social' => __('app.social_settings'),
            ] as $key => $label)
                <button
                    type="button"
                    wire:click="setTab('{{ $key }}')"
                    @class([
                        'rounded-lg px-4 py-2 text-sm font-medium transition',
                        'bg-brand text-white' => $tab === $key,
                        'bg-neutral-secondary-soft text-body hover:bg-brand-softer hover:text-brand' => $tab !== $key,
                    ])
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        @if ($tab === 'general')
            <x-fwb.card>
                <form wire:submit="saveGeneral" class="space-y-5">
                    <div>
                        <h2 class="text-lg font-semibold text-heading">{{ __('app.general_settings') }}</h2>
                        <p class="mt-1 text-sm text-body">{{ __('app.general_settings_help') }}</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-fwb.input
                                wire:model="generalForm.site_name"
                                :label="__('app.site_name')"
                                :helper="__('app.site_name_help')"
                                type="text"
                            />
                            @error('generalForm.site_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-fwb.input
                                wire:model="generalForm.site_short_name"
                                :label="__('app.site_short_name')"
                                :helper="__('app.site_short_name_help')"
                                type="text"
                                dir="ltr"
                                maxlength="4"
                            />
                            @error('generalForm.site_short_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <x-fwb.textarea
                            wire:model="generalForm.site_description"
                            :label="__('app.site_description')"
                            :helper="__('app.site_description_help')"
                            rows="3"
                        />
                        @error('generalForm.site_description')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-heading">{{ __('app.site_tags') }}</label>
                        <p class="mb-2 text-xs text-body">{{ __('app.site_tags_help') }}</p>
                        <div class="flex flex-wrap gap-2 mb-3">
                            @forelse ($generalForm->site_tags as $index => $tag)
                                <span class="inline-flex items-center gap-1 rounded-lg bg-brand-softer px-2.5 py-1 text-sm text-brand">
                                    {{ $tag }}
                                    <button type="button" wire:click="removeTag({{ $index }})" class="hover:text-red-600">
                                        <x-lucide-x class="h-3.5 w-3.5" />
                                    </button>
                                </span>
                            @empty
                                <span class="text-sm text-body">{{ __('app.no_tags') }}</span>
                            @endforelse
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <x-fwb.input
                                    wire:model="generalForm.tag_input"
                                    wire:keydown.enter.prevent="addTag"
                                    type="text"
                                    :placeholder="__('app.add_tag_placeholder')"
                                />
                            </div>
                            <x-ui.button type="button" color="cyan" wire:click="addTag" :loading="false">
                                {{ __('general.add_tag') }}
                            </x-ui.button>
                        </div>
                        @error('generalForm.site_tags')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-fwb.select
                                wire:model="generalForm.locale"
                                :label="__('app.locale')"
                                :helper="__('app.locale_help')"
                            >
                                <option value="fa">{{ __('app.locale_fa') }}</option>
                                <option value="en">{{ __('app.locale_en') }}</option>
                            </x-fwb.select>
                            @error('generalForm.locale')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-fwb.select
                                wire:model="generalForm.timezone"
                                :label="__('app.timezone')"
                                :helper="__('app.timezone_help')"
                            >
                                @foreach ($this->timezones() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-fwb.select>
                            @error('generalForm.timezone')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="mb-2 text-xs text-body">{{ __('app.site_logo_help') }}</p>
                            @if ($this->logoPreviewUrl())
                                <div class="mb-3 flex items-center gap-3 rounded-lg border border-default bg-neutral-secondary-soft p-3">
                                    <img src="{{ $this->logoPreviewUrl() }}" alt="{{ __('app.site_logo') }}" class="h-12 w-auto max-w-[160px] object-contain">
                                </div>
                            @endif
                            <x-ui.file-input
                                wire:model="generalForm.logo"
                                :label="__('app.site_logo')"
                                :helper="__('app.logo_file_help')"
                                accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                dropzone
                            />
                            @error('generalForm.logo')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <p class="mb-2 text-xs text-body">{{ __('app.favicon_help') }}</p>
                            @if ($this->faviconPreviewUrl())
                                <div class="mb-3 flex items-center gap-3 rounded-lg border border-default bg-neutral-secondary-soft p-3">
                                    <img src="{{ $this->faviconPreviewUrl() }}" alt="{{ __('app.favicon') }}" class="h-8 w-8 object-contain">
                                </div>
                            @endif
                            <x-ui.file-input
                                wire:model="generalForm.favicon"
                                :label="__('app.favicon')"
                                :helper="__('app.favicon_file_help')"
                                accept="image/png,image/x-icon,image/jpeg,image/webp"
                                dropzone
                            />
                            @error('generalForm.favicon')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <x-ui.button type="submit" color="green" target="saveGeneral" class="w-full md:w-auto">
                        {{ __('general.save') }}
                    </x-ui.button>
                </form>
            </x-fwb.card>
        @endif

        @if ($tab === 'maintenance')
            <x-fwb.card>
                <form wire:submit="saveMaintenance" class="space-y-5">
                    <div>
                        <h2 class="text-lg font-semibold text-heading">{{ __('app.maintenance_settings') }}</h2>
                        <p class="mt-1 text-sm text-body">{{ __('app.maintenance_settings_help') }}</p>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100">
                        {{ __('app.maintenance_bypass_help', ['url' => url('/'.$maintenanceForm->secret)]) }}
                    </div>

                    <x-fwb.checkbox
                        wire:model="maintenanceForm.enabled"
                        :label="__('app.maintenance_mode')"
                        :helper="__('app.maintenance_mode_help')"
                    />
                    @error('maintenanceForm.enabled')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror

                    <div>
                        <x-fwb.input
                            wire:model.live="maintenanceForm.secret"
                            :label="__('app.maintenance_secret')"
                            :helper="__('app.maintenance_secret_help')"
                            type="text"
                            dir="ltr"
                        />
                        @error('maintenanceForm.secret')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-fwb.textarea
                            wire:model="maintenanceForm.message"
                            :label="__('app.maintenance_message')"
                            :helper="__('app.maintenance_message_help')"
                            rows="4"
                        />
                        @error('maintenanceForm.message')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit" color="orange" target="saveMaintenance" class="w-full md:w-auto">
                        {{ __('general.save') }}
                    </x-ui.button>
                </form>
            </x-fwb.card>
        @endif

        @if ($tab === 'contact')
            <x-fwb.card>
                <form wire:submit="saveContact" class="space-y-5">
                    <div>
                        <h2 class="text-lg font-semibold text-heading">{{ __('app.contact_settings') }}</h2>
                        <p class="mt-1 text-sm text-body">{{ __('app.contact_settings_help') }}</p>
                    </div>

                    <div>
                        <x-fwb.textarea
                            wire:model="contactForm.address"
                            :label="__('app.address')"
                            :helper="__('app.address_help')"
                            rows="3"
                        />
                        @error('contactForm.address')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-fwb.input
                                wire:model="contactForm.postal_code"
                                :label="__('app.postal_code')"
                                :helper="__('app.postal_code_help')"
                                type="text"
                                dir="ltr"
                                maxlength="10"
                            />
                            @error('contactForm.postal_code')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-fwb.input
                                wire:model="contactForm.fax"
                                :label="__('app.fax')"
                                :helper="__('app.fax_help')"
                                type="text"
                                dir="ltr"
                                placeholder="021-12345678"
                            />
                            @error('contactForm.fax')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <x-fwb.input
                            wire:model="contactForm.support_email"
                            :label="__('app.support_email')"
                            :helper="__('app.support_email_help')"
                            type="email"
                            dir="ltr"
                        />
                        @error('contactForm.support_email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div>
                                <label class="block text-sm font-medium text-heading">{{ __('app.phones') }}</label>
                                <p class="text-xs text-body">{{ __('app.phones_help') }}</p>
                            </div>
                            <x-ui.button type="button" color="cyan" size="sm" wire:click="addPhone" :loading="false">
                                <x-slot:icon>
                                    <x-lucide-plus class="h-4 w-4 me-1" />
                                </x-slot:icon>
                                {{ __('app.add_phone') }}
                            </x-ui.button>
                        </div>

                        <div class="space-y-3">
                            @foreach ($contactForm->phones as $index => $phone)
                                <div class="flex gap-2" wire:key="phone-{{ $index }}">
                                    <div class="flex-1">
                                        <x-fwb.input
                                            wire:model="contactForm.phones.{{ $index }}"
                                            type="text"
                                            dir="ltr"
                                            :placeholder="__('app.phone_placeholder')"
                                        />
                                        @error('contactForm.phones.'.$index)
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <x-ui.button
                                        type="button"
                                        color="red"
                                        wire:click="removePhone({{ $index }})"
                                        :loading="false"
                                    >
                                        <x-lucide-trash class="h-4 w-4" />
                                    </x-ui.button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <x-ui.button type="submit" color="green" target="saveContact" class="w-full md:w-auto">
                        {{ __('general.save') }}
                    </x-ui.button>
                </form>
            </x-fwb.card>
        @endif

        @if ($tab === 'social')
            <x-fwb.card>
                <form wire:submit="saveSocial" class="space-y-5">
                    <div>
                        <h2 class="text-lg font-semibold text-heading">{{ __('app.social_settings') }}</h2>
                        <p class="mt-1 text-sm text-body">{{ __('app.social_settings_help') }}</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ([
                            'telegram' => ['label' => __('app.social_telegram'), 'help' => __('app.social_telegram_help'), 'placeholder' => 'https://t.me/username'],
                            'eitaa' => ['label' => __('app.social_eitaa'), 'help' => __('app.social_eitaa_help'), 'placeholder' => 'https://eitaa.com/username'],
                            'bale' => ['label' => __('app.social_bale'), 'help' => __('app.social_bale_help'), 'placeholder' => 'https://ble.ir/username'],
                            'rubika' => ['label' => __('app.social_rubika'), 'help' => __('app.social_rubika_help'), 'placeholder' => 'https://rubika.ir/username'],
                            'soroush' => ['label' => __('app.social_soroush'), 'help' => __('app.social_soroush_help'), 'placeholder' => 'https://splus.ir/username'],
                            'aparat' => ['label' => __('app.social_aparat'), 'help' => __('app.social_aparat_help'), 'placeholder' => 'https://aparat.com/username'],
                            'instagram' => ['label' => __('app.social_instagram'), 'help' => __('app.social_instagram_help'), 'placeholder' => 'https://instagram.com/username'],
                            'linkedin' => ['label' => __('app.social_linkedin'), 'help' => __('app.social_linkedin_help'), 'placeholder' => 'https://linkedin.com/company/name'],
                            'x' => ['label' => __('app.social_x'), 'help' => __('app.social_x_help'), 'placeholder' => 'https://x.com/username'],
                        ] as $field => $meta)
                            <div wire:key="social-{{ $field }}">
                                <x-fwb.input
                                    wire:model="socialForm.{{ $field }}"
                                    :label="$meta['label']"
                                    :helper="$meta['help']"
                                    type="url"
                                    dir="ltr"
                                    :placeholder="$meta['placeholder']"
                                />
                                @error('socialForm.'.$field)
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <x-ui.button type="submit" color="green" target="saveSocial" class="w-full md:w-auto">
                        {{ __('general.save') }}
                    </x-ui.button>
                </form>
            </x-fwb.card>
        @endif
    </div>
</div>
