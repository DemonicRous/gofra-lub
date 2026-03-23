<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeUser extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Создаем ссылку для подтверждения email
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Добро пожаловать в GofraLub! 🚀')
            ->view('emails.welcome', [
                'user' => $notifiable,
                'url' => $verificationUrl,
                'subtitle' => 'Подтвердите свой email'
            ]);
    }

    protected function verificationUrl($notifiable)
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            \Illuminate\Support\Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
