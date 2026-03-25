<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;

class DepartmentPositionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting DepartmentPositionSeeder...');

        // Создаем основные отделы
        $mainDepartments = [
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
                'name' => 'Отдел продаж',
                'code' => 'SALES',
                'description' => 'Продажи и работа с клиентами',
                'is_active' => true
            ],
        ];

        $createdDepartments = [];

        foreach ($mainDepartments as $deptData) {
            $department = Department::firstOrCreate(
                ['code' => $deptData['code']],
                $deptData
            );
            $createdDepartments[$deptData['code']] = $department;
            $this->command->info("✅ Department '{$department->name}' created (ID: {$department->id})");
        }

        // Создаем дочерние отделы для отдела развития
        $devDepartment = Department::where('code', 'DEV')->first();

        if ($devDepartment) {
            $subDepartments = [
                [
                    'name' => 'Отдел развития - Конструктора',
                    'code' => 'DEV_CONSTRUCTOR',
                    'description' => 'Конструкторский отдел',
                    'parent_id' => $devDepartment->id,
                    'is_active' => true
                ],
                [
                    'name' => 'Отдел развития - Дизайнеры',
                    'code' => 'DEV_DESIGN',
                    'description' => 'Дизайн-отдел',
                    'parent_id' => $devDepartment->id,
                    'is_active' => true
                ],
            ];

            foreach ($subDepartments as $subDeptData) {
                $subDepartment = Department::firstOrCreate(
                    ['code' => $subDeptData['code']],
                    $subDeptData
                );
                $createdDepartments[$subDeptData['code']] = $subDepartment;
                $this->command->info("✅ Sub-department '{$subDepartment->name}' created (ID: {$subDepartment->id})");
            }
        }

        // Создаем должности для каждого отдела
        $this->createPositions($createdDepartments);

        $this->command->info('=====================================');
        $this->command->info('📊 Departments created:');
        foreach ($createdDepartments as $code => $dept) {
            $this->command->info("   - {$code}: {$dept->name} (ID: {$dept->id})");
        }
        $this->command->info('=====================================');
        $this->command->info('✅ DepartmentPositionSeeder completed successfully!');
        $this->command->info('=====================================');
    }

    private function createPositions($departments): void
    {
        $this->command->info('Creating positions...');

        // IT отдел
        if (isset($departments['IT'])) {
            $itPositions = [
                ['name' => 'Руководитель IT-отдела', 'level' => 'head', 'code' => 'IT_HEAD'],
                ['name' => 'Системный администратор', 'level' => 'middle', 'code' => 'IT_SYSADMIN'],
                ['name' => 'Разработчик', 'level' => 'middle', 'code' => 'IT_DEV'],
            ];
            $this->createPositionsForDepartment($departments['IT']->id, $itPositions);
        }

        // Отдел развития (родительский) - добавляем должность руководителя
        if (isset($departments['DEV'])) {
            $devPositions = [
                ['name' => 'Руководитель отдела развития', 'level' => 'head', 'code' => 'DEV_HEAD'],
            ];
            $this->createPositionsForDepartment($departments['DEV']->id, $devPositions);
        }

        // Отдел развития - Конструктора
        if (isset($departments['DEV_CONSTRUCTOR'])) {
            $constructorPositions = [
                ['name' => 'Руководитель конструкторского отдела', 'level' => 'head', 'code' => 'DEV_CONSTRUCTOR_HEAD'],
                ['name' => 'Технолог-конструктор', 'level' => 'middle', 'code' => 'DEV_TECH_DESIGNER'],
                ['name' => 'Ведущий технолог-конструктор', 'level' => 'lead', 'code' => 'DEV_LEAD_TECH_DESIGNER'],
            ];
            $this->createPositionsForDepartment($departments['DEV_CONSTRUCTOR']->id, $constructorPositions);
        }

        // Отдел развития - Дизайнеры
        if (isset($departments['DEV_DESIGN'])) {
            $designPositions = [
                ['name' => 'Руководитель дизайн-отдела', 'level' => 'head', 'code' => 'DEV_DESIGN_HEAD'],
                ['name' => 'Дизайнер', 'level' => 'middle', 'code' => 'DEV_DESIGNER'],
                ['name' => 'Ведущий дизайнер', 'level' => 'lead', 'code' => 'DEV_LEAD_DESIGNER'],
            ];
            $this->createPositionsForDepartment($departments['DEV_DESIGN']->id, $designPositions);
        }

        // Отдел продаж
        if (isset($departments['SALES'])) {
            $salesPositions = [
                ['name' => 'Руководитель отдела продаж', 'level' => 'head', 'code' => 'SALES_HEAD'],
                ['name' => 'Front-менеджер', 'level' => 'middle', 'code' => 'SALES_FRONT'],
                ['name' => 'Back-менеджер', 'level' => 'middle', 'code' => 'SALES_BACK'],
            ];
            $this->createPositionsForDepartment($departments['SALES']->id, $salesPositions);
        }

        $this->command->info('✅ Positions created successfully!');
    }

    private function createPositionsForDepartment($departmentId, $positions): void
    {
        foreach ($positions as $posData) {
            $position = Position::firstOrCreate(
                [
                    'name' => $posData['name'],
                    'department_id' => $departmentId
                ],
                [
                    'code' => $posData['code'],
                    'level' => $posData['level'],
                    'is_active' => true
                ]
            );
            $this->command->info("   📌 Position: {$position->name} (Department ID: {$departmentId})");
        }
    }
}
