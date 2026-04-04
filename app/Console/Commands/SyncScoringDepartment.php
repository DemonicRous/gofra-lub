<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Department;
use Illuminate\Console\Command;

class SyncScoringDepartment extends Command
{
    protected $signature = 'scoring:sync-department
                            {--department= : ID или код отдела}
                            {--type=constructor : Тип подотдела (constructor/designer)}';

    protected $description = 'Назначить scoring_department пользователям на основе отдела';

    public function handle()
    {
        $departmentParam = $this->option('department');
        $type = $this->option('type');

        if (!in_array($type, ['constructor', 'designer'])) {
            $this->error('Тип должен быть constructor или designer');
            return 1;
        }

        $query = Department::query();
        if (is_numeric($departmentParam)) {
            $query->where('id', $departmentParam);
        } elseif ($departmentParam) {
            $query->where('code', $departmentParam);
        } else {
            $this->error('Не указан отдел (--department=ID или --department=CODE)');
            return 1;
        }

        $department = $query->first();

        if (!$department) {
            $this->error('Отдел не найден');
            return 1;
        }

        $users = User::where('department_id', $department->id)
            ->whereNotNull('approved_at')
            ->get();

        $count = 0;
        foreach ($users as $user) {
            if ($user->scoring_department !== $type) {
                $user->scoring_department = $type;
                $user->save();
                $count++;
            }
        }

        $this->info("Обновлено пользователей: {$count}");
        $this->info("Теперь для этих пользователей будет создана ведомость на следующий месяц.");
        $this->warn('Запустите php artisan scoring:create-sheets для создания ведомостей на текущий/следующий месяц.');

        return 0;
    }
}
