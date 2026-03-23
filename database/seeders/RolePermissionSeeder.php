<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Сброс кэша
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Создание разрешений
        $approveUsersPermission = Permission::firstOrCreate(['name' => 'approve users', 'guard_name' => 'web']);
        $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        $viewUsersPermission = Permission::firstOrCreate(['name' => 'view users', 'guard_name' => 'web']);

        // Создание ролей
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Назначение разрешений
        if (!$adminRole->hasPermissionTo('approve users')) {
            $adminRole->givePermissionTo($approveUsersPermission);
        }
        if (!$adminRole->hasPermissionTo('manage users')) {
            $adminRole->givePermissionTo($manageUsersPermission);
        }
        if (!$adminRole->hasPermissionTo('view users')) {
            $adminRole->givePermissionTo($viewUsersPermission);
        }

        if (!$managerRole->hasPermissionTo('view users')) {
            $managerRole->givePermissionTo($viewUsersPermission);
        }

        // Получаем существующие отделы (не создаем новые)
        $itDepartment = Department::where('code', 'IT')->first();
        $hrDepartment = Department::where('code', 'HR')->first();
        $developmentDepartment = Department::where('code', 'DEV')->first();
        $accountingDepartment = Department::where('code', 'ACC')->first();

        // Проверяем, что отделы существуют
        if (!$itDepartment || !$hrDepartment || !$developmentDepartment || !$accountingDepartment) {
            $this->command->error('Some departments are missing! Please run DepartmentPositionSeeder first.');
            return;
        }

        // Получаем должности
        $itHeadPosition = Position::where('name', 'Руководитель IT-отдела')
            ->where('department_id', $itDepartment->id)
            ->first();

        $hrManagerPosition = Position::where('name', 'Менеджер по персоналу')
            ->where('department_id', $hrDepartment->id)
            ->first();

        $devSpecialistPosition = Position::where('name', 'Специалист по развитию')
            ->where('department_id', $developmentDepartment->id)
            ->first();

        // Создание администратора
        $admin = User::where('email', 'admin@gofralub.ru')->first();
        if (!$admin) {
            $nickname = User::generateNicknameFromEmail('admin@gofralub.ru');

            $admin = User::create([
                'last_name' => 'Администратор',
                'first_name' => 'Системный',
                'patronymic' => null,
                'nickname' => $nickname,
                'department_id' => $itDepartment->id,
                'position_id' => $itHeadPosition ? $itHeadPosition->id : null,
                'email' => 'admin@gofralub.ru',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'approved_at' => now(),
            ]);
            $admin->assignRole('admin');
            $this->command->info('Admin user created successfully!');
        } else {
            $this->command->info('Admin user already exists, skipping...');
        }

        // Создание тестового менеджера
        $manager = User::where('email', 'manager@gofralub.ru')->first();
        if (!$manager && $hrManagerPosition) {
            $nickname = User::generateNicknameFromEmail('manager@gofralub.ru');

            $manager = User::create([
                'last_name' => 'Менеджер',
                'first_name' => 'Тестовый',
                'patronymic' => 'Тестович',
                'nickname' => $nickname,
                'department_id' => $hrDepartment->id,
                'position_id' => $hrManagerPosition->id,
                'email' => 'manager@gofralub.ru',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'approved_at' => now(),
            ]);
            $manager->assignRole('manager');
            $this->command->info('Manager user created successfully!');
        } elseif ($manager) {
            $this->command->info('Manager user already exists, skipping...');
        }

        // Создание тестового пользователя
        $testUser = User::where('email', 'user@yandex.ru')->first();
        if (!$testUser && $devSpecialistPosition) {
            $nickname = User::generateNicknameFromEmail('user@yandex.ru');

            $testUser = User::create([
                'last_name' => 'Пользователь',
                'first_name' => 'Тестовый',
                'patronymic' => 'Тестович',
                'nickname' => $nickname,
                'department_id' => $developmentDepartment->id,
                'position_id' => $devSpecialistPosition->id,
                'email' => 'user@yandex.ru',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'approved_at' => now(),
            ]);
            $testUser->assignRole('user');
            $this->command->info('Test user created successfully!');
        } elseif ($testUser) {
            $this->command->info('Test user already exists, skipping...');
        }

        // Создание пользователя, ожидающего одобрения
        $pendingUser = User::where('email', 'pending@yandex.ru')->first();
        if (!$pendingUser && $devSpecialistPosition) {
            $nickname = User::generateNicknameFromEmail('pending@yandex.ru');

            $pendingUser = User::create([
                'last_name' => 'Ожидающий',
                'first_name' => 'Пользователь',
                'patronymic' => null,
                'nickname' => $nickname,
                'department_id' => $developmentDepartment->id,
                'position_id' => $devSpecialistPosition->id,
                'email' => 'pending@yandex.ru',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'approved_at' => null,
            ]);
            $pendingUser->assignRole('user');
            $this->command->info('Pending user created successfully!');
        } elseif ($pendingUser) {
            $this->command->info('Pending user already exists, skipping...');
        }

        $this->command->info('=====================================');
        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('=====================================');
        $this->command->info('Available users:');
        $this->command->info('Admin: admin@gofralub.ru / password');
        $this->command->info('Manager: manager@gofralub.ru / password');
        $this->command->info('User: user@yandex.ru / password');
        $this->command->info('Pending: pending@yandex.ru / password (awaiting approval)');
        $this->command->info('=====================================');
    }
}
