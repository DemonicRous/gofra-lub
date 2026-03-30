<?php
// app/Console/Commands/CheckScoringSheets.php

namespace App\Console\Commands;

use App\Models\ScoringSheet;
use App\Notifications\ScoringSheetReminder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckScoringSheets extends Command
{
    protected $signature = 'scoring:check-sheets {--notify : Отправить уведомления}';
    protected $description = 'Check scoring sheets status';

    public function handle()
    {
        $this->info('Checking scoring sheets...');

        $currentMonth = Carbon::now()->startOfMonth();

        // Проверяем незаполненные ведомости за текущий месяц
        $draftSheets = ScoringSheet::where('status', 'draft')
            ->where('period_date', $currentMonth)
            ->with('user')
            ->get();

        $this->info("Найдено незаполненных ведомостей: {$draftSheets->count()}");

        if ($this->option('notify') && $draftSheets->count() > 0) {
            $this->info('Отправка уведомлений...');
            $bar = $this->output->createProgressBar($draftSheets->count());

            foreach ($draftSheets as $sheet) {
                $sheet->user->notify(new ScoringSheetReminder($sheet));
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('Уведомления отправлены');
        }

        // Статистика
        $stats = [
            'draft' => ScoringSheet::where('status', 'draft')->count(),
            'confirmed' => ScoringSheet::where('status', 'confirmed')->count(),
            'approved' => ScoringSheet::where('status', 'approved')->count(),
        ];

        $this->table(
            ['Статус', 'Количество'],
            collect($stats)->map(fn($count, $status) => [$status, $count])
        );

        return 0;
    }
}
