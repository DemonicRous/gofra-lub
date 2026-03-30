<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Важно: сначала создаем отделы и должности
        $this->call(DepartmentPositionSeeder::class);

        // Затем создаем роли, разрешения и пользователей
        $this->call(RolePermissionSeeder::class);

        $this->call(ManagerSeeder::class);
    }
}
