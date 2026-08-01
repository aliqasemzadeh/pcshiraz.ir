<?php

namespace App\Notifications;

use App\Jobs\Notification\SendSmsMessageJob;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Spatie\OneTimePasswords\Notifications\OneTimePasswordNotification;

class CustomOneTimePasswordNotification extends OneTimePasswordNotification
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['text-message'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toTextMessage(object $notifiable): array
    {
        if (config('app.debug')) {
            Log::debug('OTP for debug', [
                'mobile' => $notifiable->mobile,
                'code' => $this->oneTimePassword->password,
            ]);
        }

        $message = __('app.otp_message', [
            'code' => $this->oneTimePassword->password,
            'app_name' => config('app.name'),
            'app_url' => parse_url((string) config('app.url'), PHP_URL_HOST) ?: config('app.url'),
        ]);

        SendSmsMessageJob::dispatchSync(
            $notifiable->mobile,
            $message
        );

        return [
            'message' => $message,
        ];
    }
}
