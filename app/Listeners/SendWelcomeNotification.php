<?php

namespace App\Listeners;

use App\Notifications\WelcomeUser;
use Illuminate\Auth\Events\Verified;

class SendWelcomeNotification
{
    public function handle(Verified $event)
    {
        $event->user->notify(new WelcomeUser());
    }
}
