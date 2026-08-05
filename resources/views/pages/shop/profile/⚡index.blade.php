<?php

use App\Livewire\Forms\ProfileForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new #[Layout('layouts.app')] class extends Component
{
    public ProfileForm $form;

    public function mount(): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->form->setUser(Auth::user());
    }

    #[Computed]
    public function identityVerified(): bool
    {
        return Auth::user()?->isIdentityVerified() ?? false;
    }

    public function save(): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $wasVerified = $user->isIdentityVerified();

        $this->form->update($user);
        $user = $user->fresh();
        Auth::setUser($user);
        $this->form->setUser($user);
        unset($this->identityVerified);

        Toaster::success(
            ! $wasVerified && $user->isIdentityVerified()
                ? __('general.identity_verified')
                : __('general.saved')
        );
    }
};
?>

<div class="flex min-h-[60vh] flex-col gap-6">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('general.profile_settings') }}</h1>

        @guest
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('general.login_or_register') }}</p>

            <a
                href="{{ route('login') }}"
                wire:navigate
                class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-strong focus:ring-4 focus:ring-brand/30 focus:outline-none dark:bg-brand dark:hover:bg-brand-strong dark:focus:ring-brand/40"
            >
                <x-lucide-log-in class="h-4 w-4" />
                {{ __('general.login') }}
            </a>
        @endguest

        @auth
            @if ($this->identityVerified)
                <div class="mt-4 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
                    <x-lucide-badge-check class="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <p class="font-medium">{{ __('general.identity_verified') }}</p>
                        <p class="mt-1 opacity-90">{{ __('general.identity_verified_message') }}</p>
                    </div>
                </div>
            @endif

            <form wire:submit="save" class="mt-6 space-y-8">
                <section class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('general.personal_info') }}</h2>
                    </div>

                    <div>
                        <x-fwb.input
                            wire:model="form.mobile"
                            :label="__('general.mobile')"
                            type="text"
                            dir="ltr"
                            inputmode="numeric"
                            :readonly="$this->identityVerified"
                            @class(['opacity-70' => $this->identityVerified])
                        />
                        @error('form.mobile')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-fwb.input
                                wire:model="form.first_name"
                                :label="__('general.first_name')"
                                type="text"
                                :readonly="$this->identityVerified"
                                @class(['opacity-70' => $this->identityVerified])
                            />
                            @error('form.first_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-fwb.input
                                wire:model="form.last_name"
                                :label="__('general.last_name')"
                                type="text"
                                :readonly="$this->identityVerified"
                                @class(['opacity-70' => $this->identityVerified])
                            />
                            @error('form.last_name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <x-fwb.input
                            wire:model="form.national_code"
                            :label="__('general.national_code')"
                            type="text"
                            dir="ltr"
                            inputmode="numeric"
                            maxlength="10"
                            :readonly="$this->identityVerified"
                            @class(['opacity-70' => $this->identityVerified])
                        />
                        @error('form.national_code')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-ui.jalali-datepicker
                            wire:model="form.birth_date"
                            :label="__('general.birth_date')"
                            max-date="today"
                            :readonly="$this->identityVerified"
                            @class(['opacity-70' => $this->identityVerified])
                        />
                        @error('form.birth_date')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-fwb.input
                            wire:model="form.email"
                            :label="__('general.email')"
                            type="email"
                            dir="ltr"
                            :readonly="$this->identityVerified"
                            @class(['opacity-70' => $this->identityVerified])
                        />
                        @error('form.email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                @unless ($this->identityVerified)
                    <section class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
                        <div class="flex items-start gap-3">
                            <x-lucide-shield-check class="mt-0.5 h-5 w-5 shrink-0 text-amber-700 dark:text-amber-300" />
                            <div class="flex-1 space-y-3">
                                <div>
                                    <h2 class="text-base font-semibold text-amber-900 dark:text-amber-100">{{ __('general.identity_verification') }}</h2>
                                    <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">{{ __('general.identity_verification_help') }}</p>
                                    <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">{{ __('general.identity_verification_required_fields') }}</p>
                                </div>

                                <x-fwb.checkbox
                                    wire:model="form.request_identity_verification"
                                    :label="__('general.identity_verification')"
                                />
                                @error('form.request_identity_verification')
                                    <p class="text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                @endunless

                <section class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('general.account_security') }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('general.password_optional_help') }}</p>
                    </div>

                    <div>
                        <x-fwb.input
                            wire:model="form.password"
                            :label="__('general.new_password')"
                            type="password"
                            dir="ltr"
                            autocomplete="new-password"
                        />
                        @error('form.password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-fwb.input
                            wire:model="form.password_confirmation"
                            :label="__('general.password_confirmation')"
                            type="password"
                            dir="ltr"
                            autocomplete="new-password"
                        />
                        @error('form.password_confirmation')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </section>

                <x-ui.button type="submit" color="green" target="save" class="w-full">
                    {{ __('general.save') }}
                </x-ui.button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-800 focus:ring-4 focus:ring-red-300 focus:outline-none dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                    <x-lucide-log-out class="h-4 w-4" />
                    {{ __('general.logout') }}
                </button>
            </form>
        @endauth
    </div>

    <div class="mt-auto flex flex-col items-center gap-2 border-t border-gray-200 pt-6 dark:border-gray-700">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('general.theme') }}</p>
        @include('partials.layouts.theme')
    </div>
</div>
