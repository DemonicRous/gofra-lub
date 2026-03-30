<?php
// app/Console/Commands/CreateScoringSheets.php

namespace App\Console\Commands;

use App\Services\Scoring\SheetService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CreateScoringSheets extends Command
{
    protected $signature = 'scoring:create-sheets
                            {--month= : Месяц для создания ведомостей (Y-m)}
                            {--user= : ID пользователя, для которого создать ведомость}';

    protected $description = 'Create scoring sheets for employees';

    protected $sheetService;

    public function __construct(SheetService $sheetService)
    {
        parent::__construct();
        $this->sheetService = $sheetService;
    }

    public function handle()
    {
        $month = $this->option('month');
        $userId = $this->option('user');

        if ($userId) {
            // Создаем ведомость для конкретного пользователя
            $user = \App\Models\User::find($userId);
            if (!$user) {
                $this->error("Пользователь с ID {$userId} не найден");
                return 1;
            }

            $date = $month ? Carbon::parse($month) : Carbon::now()->startOfMonth();
            $this->sheetService->createSheetForUser($user, $date);
            $this->info("Ведомость для {$user->full_name} на {$date->format('F Y')} создана");

        } elseif ($month) {
            // Создаем ведомости для всех на указанный месяц
            $date = Carbon::parse($month);
            $this->createSheetsForMonth($date);

        } else {
            // Создаем ведомости на следующий месяц
            $this->info('Creating scoring sheets for next month...');
            $this->sheetService->createSheetsForNextMonth();
            $this->info('Scoring sheets created successfully!');
        }

        return 0;
    }

    /**
     * Создать ведомости для всех сотрудников на указанный месяц
     */
    private function createSheetsForMonth(Carbon $date): void
    {
        $users = \App\Models\User::whereNotNull('scoring_department')
            ->whereNotNull('approved_at')
            ->get();

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            $this->sheetService->createSheetForUser($user, $date);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Ведомости на {$date->format('F Y')} созданы для " . count($users) . " сотрудников");
    }
}
