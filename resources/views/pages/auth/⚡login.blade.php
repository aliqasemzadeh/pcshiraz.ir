<?php

use App\Livewire\Forms\AuthForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Spatie\OneTimePasswords\Actions\ConsumeOneTimePasswordAction;
use Spatie\OneTimePasswords\Enums\ConsumeOneTimePasswordResult;

new #[Layout('layouts.auth')] class extends Component
{
    public AuthForm $form;

    public bool $otpSent = false;

    public function sendOtp(): void
    {
        $this->form->validateOnly('mobile');

        $user = $this->form->getUser();

        if ($user->oneTimePasswords()->where('expires_at', '>', now())->exists()) {
            Toaster::warning(__('general.otp_still_valid'));
            $this->otpSent = true;

            return;
        }

        $user->sendOneTimePassword();
        $this->otpSent = true;

        Toaster::success(__('general.otp_sent'));
    }

    public function login(ConsumeOneTimePasswordAction $consumeOneTimePasswordAction)
    {
        $this->validate([
            'form.code' => 'required|digits:6',
        ]);

        $user = $this->form->getUser();

        $result = $consumeOneTimePasswordAction->execute($user, $this->form->code, request());

        if ($result === ConsumeOneTimePasswordResult::Ok) {
            Auth::login($user, true);

            return redirect()->intended(config('one-time-passwords.redirect_successful_authentication_to'));
        }

        $this->addError('form.code', __('general.invalid_otp'));
    }

    public function resetForm(): void
    {
        $this->otpSent = false;
        $this->form->code = '';
        $this->resetErrorBag('form.code');
    }
};
?>

<div>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
            {{ __('general.login_or_register') }}
        </h1>
    </div>

    @if (! $otpSent)
        <form wire:submit="sendOtp" class="space-y-6">
            <div>
                <label for="mobile" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                    {{ __('general.mobile') }}
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
                        <x-lucide-smartphone class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                    </div>
                    <input
                        id="mobile"
                        type="tel"
                        wire:model="form.mobile"
                        placeholder="09123456789"
                        class="block w-full rounded-lg border border-slate-300 bg-white p-2.5 ps-10 text-sm text-navbar-fg focus:border-brand focus:ring-brand dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        autocomplete="tel"
                    >
                </div>
                @error('form.mobile')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-brand px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-brand-strong focus:ring-4 focus:ring-brand/30 focus:outline-none dark:bg-brand dark:hover:bg-brand-strong dark:focus:ring-brand/40"
            >
                {{ __('general.send_otp') }}
            </button>
        </form>
    @else
        <form wire:submit="login" class="space-y-6">
            <div class="space-y-4 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('general.enter_otp') }}
                    <span class="font-medium text-gray-900 dark:text-white" dir="ltr">{{ $form->mobile }}</span>
                </p>

                <div>
                    <label for="otp-code" class="sr-only">{{ __('general.otp_code') }}</label>
                    <input
                        id="otp-code"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        autocomplete="one-time-code"
                        wire:model="form.code"
                        class="mx-auto block w-full max-w-xs rounded-lg border border-slate-300 bg-white p-3 text-center text-2xl tracking-[0.4em] text-navbar-fg focus:border-brand focus:ring-brand dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="------"
                        dir="ltr"
                    >
                    @error('form.code')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-2">
                <button
                    type="submit"
                    class="w-full rounded-lg bg-brand px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-brand-strong focus:ring-4 focus:ring-brand/30 focus:outline-none dark:bg-brand dark:hover:bg-brand-strong dark:focus:ring-brand/40"
                >
                    {{ __('general.verify_and_login') }}
                </button>

                <div class="flex justify-between gap-2">
                    <button
                        type="button"
                        wire:click="resetForm"
                        class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <x-lucide-pencil class="h-4 w-4" />
                        {{ __('general.change_mobile') }}
                    </button>

                    <button
                        type="button"
                        wire:click="sendOtp"
                        class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <x-lucide-refresh-cw class="h-4 w-4" />
                        {{ __('general.resend_otp') }}
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
