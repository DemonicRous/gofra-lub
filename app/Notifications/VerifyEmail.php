<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends BaseVerifyEmail
{
    use Queueable;

    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Подтверждение email | GofraLub')
            ->view('emails.verify-email', [
                'user' => $notifiable,
                'url' => $verificationUrl,
                'subtitle' => 'Завершите регистрацию'
            ]);
    }
}
