<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

class TextMessageChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $notification->toTextMessage($notifiable);
    }
}
