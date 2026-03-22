<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Сброс кэша
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Создание разрешений (только если не существуют)
        $approveUsersPermission = Permission::firstOrCreate(['name' => 'approve users', 'guard_name' => 'web']);

        // Дополнительные разрешения (опционально)
        $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        $viewUsersPermission = Permission::firstOrCreate(['name' => 'view users', 'guard_name' => 'web']);

        // Создание ролей
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Назначение разрешений администратору
        if (!$adminRole->hasPermissionTo('approve users')) {
            $adminRole->givePermissionTo($approveUsersPermission);
        }
        if (!$adminRole->hasPermissionTo('manage users')) {
            $adminRole->givePermissionTo($manageUsersPermission);
        }
        if (!$adminRole->hasPermissionTo('view users')) {
            $adminRole->givePermissionTo($viewUsersPermission);
        }

        // Назначение разрешений менеджеру
        if (!$managerRole->hasPermissionTo('view users')) {
            $managerRole->givePermissionTo($viewUsersPermission);
        }

        // Создание администратора (если не существует)
        $admin = User::where('email', 'admin@gofralub.ru')->first();
        if (!$admin) {
            // Генерируем никнейм из email
            $nickname = User::generateNicknameFromEmail('admin@gofralub.ru');

            $admin = User::create([
                'last_name' => 'Администратор',
                'first_name' => 'Системный',
                'patronymic' => null,
                'nickname' => $nickname,
                'position' => 'Главный администратор',
                'department' => 'IT-отдел',
                'email' => 'admin@gofralub.ru',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'approved_at' => now(),
            ]);
            $admin->assignRole('admin');
            $this->command->info('Admin user created successfully!');
        }

        // Создание тестового менеджера (опционально)
        $manager = User::where('email', 'manager@gofralub.ru')->first();
        if (!$manager) {
            $nickname = User::generateNicknameFromEmail('manager@gofralub.ru');

            $manager = User::create([
                'last_name' => 'Менеджер',
                'first_name' => 'Тестовый',
                'patronymic' => 'Тестович',
                'nickname' => $nickname,
                'position' => 'Руководитель отдела',
                'department' => 'Отдел кадров',
                'email' => 'manager@sybox.ru',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'approved_at' => now(),
            ]);
            $manager->assignRole('manager');
            $this->command->info('Manager user created successfully!');
        }

        // Создание тестового пользователя (опционально)
        $testUser = User::where('email', 'user@yandex.ru')->first();
        if (!$testUser) {
            $nickname = User::generateNicknameFromEmail('user@yandex.ru');

            $testUser = User::create([
                'last_name' => 'Пользователь',
                'first_name' => 'Тестовый',
                'patronymic' => 'Тестович',
                'nickname' => $nickname,
                'position' => 'Сотрудник',
                'department' => 'Бухгалтерия',
                'email' => 'user@yandex.ru',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'approved_at' => now(), // Одобрен автоматически
            ]);
            $testUser->assignRole('user');
            $this->command->info('Test user created successfully!');
        }

        // Создание пользователя, ожидающего одобрения (опционально)
        $pendingUser = User::where('email', 'pending@yandex.ru')->first();
        if (!$pendingUser) {
            $nickname = User::generateNicknameFromEmail('pending@yandex.ru');

            $pendingUser = User::create([
                'last_name' => 'Ожидающий',
                'first_name' => 'Пользователь',
                'patronymic' => null,
                'nickname' => $nickname,
                'position' => 'Стажер',
                'department' => 'Отдел разработки',
                'email' => 'pending@uralkarton.ru',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'approved_at' => null, // Не одобрен
            ]);
            $pendingUser->assignRole('user');
            $this->command->info('Pending user created successfully!');
        }

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Available users:');
        $this->command->info('Admin: admin@gofralub.ru / password');
        $this->command->info('Manager: manager@gofralub.ru / password');
        $this->command->info('User: user@yandex.ru / password');
        $this->command->info('Pending: pending@yandex.ru / password (awaiting approval)');
    }
}
