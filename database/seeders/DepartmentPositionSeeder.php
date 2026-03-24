<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;

class DepartmentPositionSeeder extends Seeder
{
    public function run(): void
    {
        // Отделы - используем firstOrCreate для избежания дубликатов
        $departments = [
            [
                'name' => 'IT-отдел',
                'code' => 'IT',
                'description' => 'Информационные технологии и автоматизация',
                'is_active' => true
            ],
            [
                'name' => 'Отдел развития',
                'code' => 'DEV',
                'description' => 'Развитие компании и новые направления',
                'is_active' => true
            ],
            [
                'name' => 'Отдел кадров',
                'code' => 'HR',
                'description' => 'Управление персоналом',
                'is_active' => true
            ],
            [
                'name' => 'Бухгалтерия',
                'code' => 'ACC',
                'description' => 'Финансовый учет и отчетность',
                'is_active' => true
            ],
            [
                'name' => 'Производственный отдел',
                'code' => 'PROD',
                'description' => 'Управление производством',
                'is_active' => true
            ],
        ];

        foreach ($departments as $deptData) {
            // Используем firstOrCreate вместо create
            $department = Department::firstOrCreate(
                ['code' => $deptData['code']], // Условие поиска
                $deptData // Данные для создания, если не найдено
            );

            $this->command->info("Department '{$department->name}' processed");

            // Должности для каждого отдела (без зарплат)
            $positions = match ($deptData['code']) {
                'IT' => [
                    ['name' => 'Руководитель IT-отдела', 'level' => 'head', 'code' => 'IT_HEAD'],
                    ['name' => 'Ведущий разработчик', 'level' => 'lead', 'code' => 'IT_LEAD'],
                    ['name' => 'Разработчик', 'level' => 'middle', 'code' => 'IT_DEV'],
                    ['name' => 'Младший разработчик', 'level' => 'junior', 'code' => 'IT_JUNIOR'],
                    ['name' => 'Системный администратор', 'level' => 'middle', 'code' => 'IT_SYSADMIN'],
                ],
                'DEV' => [
                    ['name' => 'Руководитель отдела развития', 'level' => 'head', 'code' => 'DEV_HEAD'],
                    ['name' => 'Ведущий специалист по развитию', 'level' => 'lead', 'code' => 'DEV_LEAD'],
                    ['name' => 'Специалист по развитию', 'level' => 'middle', 'code' => 'DEV_SPEC'],
                    ['name' => 'Младший специалист по развитию', 'level' => 'junior', 'code' => 'DEV_JUNIOR'],
                ],
                'HR' => [
                    ['name' => 'Руководитель отдела кадров', 'level' => 'head', 'code' => 'HR_HEAD'],
                    ['name' => 'Менеджер по персоналу', 'level' => 'middle', 'code' => 'HR_MGR'],
                    ['name' => 'Специалист по кадрам', 'level' => 'junior', 'code' => 'HR_SPEC'],
                ],
                'ACC' => [
                    ['name' => 'Главный бухгалтер', 'level' => 'head', 'code' => 'ACC_HEAD'],
                    ['name' => 'Бухгалтер', 'level' => 'middle', 'code' => 'ACC_ACC'],
                    ['name' => 'Помощник бухгалтера', 'level' => 'junior', 'code' => 'ACC_JUNIOR'],
                ],
                'PROD' => [
                    ['name' => 'Начальник производства', 'level' => 'head', 'code' => 'PROD_HEAD'],
                    ['name' => 'Старший мастер', 'level' => 'senior', 'code' => 'PROD_SENIOR'],
                    ['name' => 'Мастер смены', 'level' => 'middle', 'code' => 'PROD_MASTER'],
                    ['name' => 'Оператор линии', 'level' => 'junior', 'code' => 'PROD_OPERATOR'],
                ],
                default => [],
            };

            foreach ($positions as $posData) {
                // Используем firstOrCreate для должностей
                Position::firstOrCreate(
                    [
                        'name' => $posData['name'],
                        'department_id' => $department->id
                    ],
                    [
                        'code' => $posData['code'],
                        'level' => $posData['level'],
                        'is_active' => true
                    ]
                );
            }

            $this->command->info("Positions for '{$department->name}' processed");
        }

        $this->command->info('=====================================');
        $this->command->info('Departments and positions seeded successfully!');
        $this->command->info('=====================================');
    }
}
