<?php

namespace App\Providers;

use App\Events\MonthEnd;
use App\Listeners\CreateScoringSheetsListener;
use App\Listeners\SendWelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
       Verified::class => [
            SendWelcomeNotification::class,
        ],
        MonthEnd::class => [
           CreateScoringSheetsListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Регистрируем событие окончания месяца
        $this->registerMonthEndEvent();
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    /**
     * Регистрируем событие окончания месяца
     */
    protected function registerMonthEndEvent(): void
    {
        // Проверяем в конце каждого дня, не наступило ли окончание месяца
        $this->app->terminating(function () {
            $today = now();
            $tomorrow = now()->addDay();

            // Если сегодня последний день месяца
            if ($today->isLastOfMonth()) {
                event(new \App\Events\MonthEnd($today));
            }
        });
    }
}
