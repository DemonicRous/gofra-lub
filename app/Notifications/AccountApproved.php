<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AccountApproved extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Аккаунт одобрен | GofraLub')
            ->view('emails.account-approved', [
                'user' => $notifiable,
                'subtitle' => 'Доступ открыт'
            ]);
    }
}
