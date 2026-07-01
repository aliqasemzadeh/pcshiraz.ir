<?php

use App\Livewire\Forms\AuthForm;
use FluxUI\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\OneTimePasswords\Actions\ConsumeOneTimePasswordAction;
use Spatie\OneTimePasswords\Enums\ConsumeOneTimePasswordResult;

new #[Layout('layouts::auth')] class extends Component
{
    public AuthForm $form;
    public bool $otpSent = false;

    public function sendOtp()
    {
        $this->form->validateOnly('mobile');

        $user = $this->form->getUser();

        $user->sendOneTimePassword();

        $this->otpSent = true;

        Flux::toast(__('main.otp_sent'));
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

        $this->addError('form.code', __('main.invalid_otp'));
    }

    public function resetForm()
    {
        $this->otpSent = false;
        $this->form->code = '';
    }
};
?>

<div>
    <div class="mb-6 text-center">
        <flux:heading size="xl">{{ __('main.login_or_register') }}</flux:heading>
    </div>

    @if (!$otpSent)
        <form wire:submit="sendOtp" class="space-y-6">
            <flux:input
                wire:model="form.mobile"
                label="{{ __('main.mobile') }}"
                placeholder="09123456789"
                icon="smartphone"
            />

            <flux:button type="submit" variant="primary" color="teal" class="w-full">
                {{ __('main.send_otp') }}
            </flux:button>
        </form>
    @else
        <form wire:submit="login" class="space-y-6">
            <div class="text-center space-y-4">
                <flux:subheading>
                    {{ __('main.enter_otp') }} {{ $form->mobile }}
                </flux:subheading>

                <flux:otp wire:model="form.code" length="6" submit="auto" class="mx-auto" />
                <flux:error name="form.code" />
            </div>

            <div class="space-y-2">
                <flux:button type="submit" variant="primary" color="teal" class="w-full">
                    {{ __('main.verify_and_login') }}
                </flux:button>

                <div class="flex justify-between gap-2">
                    <flux:button variant="ghost" size="sm" wire:click="resetForm" icon="pencil">
                        {{ __('main.change_mobile') }}
                    </flux:button>

                    <flux:button variant="ghost" size="sm" wire:click="sendOtp" icon="refresh-cw">
                        {{ __('main.resend_otp') }}
                    </flux:button>
                </div>
            </div>
        </form>
    @endif
</div>
