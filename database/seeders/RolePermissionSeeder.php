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

        // Создание ролей
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Назначение разрешения администратору
        if (!$adminRole->hasPermissionTo('approve users')) {
            $adminRole->givePermissionTo($approveUsersPermission);
        }

        // Создание администратора (если не существует)
        $admin = User::where('email', 'admin@example.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@gofralub.ru',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'approved_at' => now(),
            ]);
            $admin->assignRole('admin');
        }
    }
}
