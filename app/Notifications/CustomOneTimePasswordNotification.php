<?php

namespace App\Notifications;

use App\Jobs\Notification\SendSmsMessageJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Spatie\OneTimePasswords\Notifications\OneTimePasswordNotification;

class CustomOneTimePasswordNotification extends OneTimePasswordNotification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['text-message'];
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toTextMessage(object $notifiable): array
    {
        $domain = $notifiable->domains()->first();

        $domainTitle = $domain?->title ?? config('app.name');
        $domainUrl = $domain?->domain ?? parse_url(config('app.url'), PHP_URL_HOST);

        $message = __('main.otp_message', [
            'code' => $this->oneTimePassword->password,
            'domain_title' => $domainTitle,
            'domain_url' => $domainUrl,
        ]);

        SendSmsMessageJob::dispatch(
            $notifiable->mobile,
            $message
        );

        return [
            'message' => $message,
        ];
    }
}
