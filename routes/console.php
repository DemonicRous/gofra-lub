<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ==================== ПЛАНИРОВЩИК ЗАДАЧ ====================

// Создание ведомостей на следующий месяц (в последний день месяца)
Schedule::command('scoring:create-sheets')
    ->monthlyOn(28, '23:55')
    ->description('Создание ведомостей на следующий месяц');

// Альтернатива: в последний день месяца
Schedule::command('scoring:create-sheets')
    ->lastDayOfMonth('23:55')
    ->description('Создание ведомостей на следующий месяц');

// Проверка и отправка напоминаний о неподтвержденных ведомостях (каждый день в 10:00)
Schedule::call(function () {
    $sheets = \App\Models\ScoringSheet::where('status', 'draft')
        ->where('period_date', '<=', now()->startOfMonth())
        ->with('user')
        ->get();

    foreach ($sheets as $sheet) {
        // Отправляем напоминание сотруднику
        $sheet->user->notify(new ScoringSheetReminder($sheet));
    }
})->dailyAt('10:00')->description('Напоминания о незаполненных ведомостях');

// Очистка старых кэшей (раз в неделю)
Schedule::command('cache:prune-stale-tags')->weekly();

// Бэкап базы данных (раз в день)
if (app()->environment('production')) {
    Schedule::command('backup:run --only-db')
        ->daily()
        ->at('02:00')
        ->description('Ежедневный бэкап базы данных');
}
