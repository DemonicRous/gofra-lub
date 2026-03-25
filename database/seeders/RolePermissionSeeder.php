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
    private array $users = [];
    private array $departments = [];
    private array $positions = [];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Starting RolePermissionSeeder...');

        // Загружаем существующие отделы и должности
        $this->loadReferences();

        $this->createPermissions();
        $this->createRoles();
        $this->createUsers();

        $this->command->info('=====================================');
        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('=====================================');
        $this->displayUsersInfo();
    }

    private function loadReferences(): void
    {
        $this->command->info('Loading departments and positions...');

        // Загружаем отделы
        $this->departments['IT'] = Department::where('code', 'IT')->first();
        $this->departments['DEV'] = Department::where('code', 'DEV')->first();
        $this->departments['SALES'] = Department::where('code', 'SALES')->first();
        $this->departments['DEV_CONSTRUCTOR'] = Department::where('code', 'DEV_CONSTRUCTOR')->first();
        $this->departments['DEV_DESIGN'] = Department::where('code', 'DEV_DESIGN')->first();

        // Проверяем, что все отделы найдены
        $missingDepartments = [];
        foreach ($this->departments as $key => $dept) {
            if (!$dept) {
                $missingDepartments[] = $key;
            }
        }

        if (!empty($missingDepartments)) {
            $this->command->error('Missing departments: ' . implode(', ', $missingDepartments));
            $this->command->error('Please run DepartmentPositionSeeder first!');
            return;
        }

        $this->command->info('✅ Departments loaded successfully:');
        foreach ($this->departments as $key => $dept) {
            $this->command->info("   - {$key}: {$dept->name} (ID: {$dept->id})");
        }

        // Загружаем должности
        $this->positions['IT_HEAD'] = Position::where('code', 'IT_HEAD')->first();
        $this->positions['DEV_HEAD'] = Position::where('code', 'DEV_HEAD')->first();
        $this->positions['SALES_HEAD'] = Position::where('code', 'SALES_HEAD')->first();
        $this->positions['DEV_TECH_DESIGNER'] = Position::where('code', 'DEV_TECH_DESIGNER')->first();
        $this->positions['DEV_LEAD_TECH_DESIGNER'] = Position::where('code', 'DEV_LEAD_TECH_DESIGNER')->first();
        $this->positions['DEV_DESIGNER'] = Position::where('code', 'DEV_DESIGNER')->first();
        $this->positions['DEV_LEAD_DESIGNER'] = Position::where('code', 'DEV_LEAD_DESIGNER')->first();
        $this->positions['SALES_FRONT'] = Position::where('code', 'SALES_FRONT')->first();
        $this->positions['SALES_BACK'] = Position::where('code', 'SALES_BACK')->first();

        $this->command->info('✅ Positions loaded successfully');
    }

    private function createPermissions(): void
    {
        $this->command->info('Creating permissions...');

        $permissions = [
            'approve users',
            'manage users',
            'view users',
            'create todos',
            'edit todos',
            'delete todos',
            'view all todos',
            'manage department todos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $this->command->info("   🔐 Permission: {$permission}");
        }
    }

    private function createRoles(): void
    {
        $this->command->info('Creating roles...');

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Админ - все права
        $adminRole->syncPermissions(Permission::all());
        $this->command->info("   👑 Role: admin (all permissions)");

        // Менеджер - права на управление задачами отдела и просмотр пользователей
        $managerRole->syncPermissions([
            'view users',
            'create todos',
            'edit todos',
            'manage department todos',
        ]);
        $this->command->info("   📋 Role: manager (department todos + view users)");

        // Обычный пользователь - только создание личных задач
        $userRole->syncPermissions(['create todos']);
        $this->command->info("   👤 Role: user (personal todos only)");
    }

    private function createUsers(): void
    {
        $this->command->info('Creating users...');

        // ==================== АДМИНИСТРАТОР ====================
        $this->users['admin'] = $this->createUserIfNotExists([
            'last_name' => 'Администратор',
            'first_name' => 'Системный',
            'email' => 'admin@gofralub.ru',
            'department_id' => $this->departments['IT']?->id,
            'position_id' => $this->positions['IT_HEAD']?->id,
        ], 'admin', true, true);

        // ==================== IT ОТДЕЛ ====================
        $this->users['it_head'] = $this->createUserIfNotExists([
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'patronymic' => 'Иванович',
            'email' => 'it.head@sybox.ru',
            'department_id' => $this->departments['IT']?->id,
            'position_id' => $this->positions['IT_HEAD']?->id,
        ], 'user', true, true);

        // ==================== ОТДЕЛ РАЗВИТИЯ (родительский отдел) ====================
        // Руководитель отдела развития (находится в родительском отделе DEV)
        $this->users['dev_head_main'] = $this->createUserIfNotExists([
            'last_name' => 'Сергеев',
            'first_name' => 'Сергей',
            'patronymic' => 'Сергеевич',
            'email' => 'dev.head@sybox.ru',
            'department_id' => $this->departments['DEV']?->id,
            'position_id' => $this->positions['DEV_HEAD']?->id,
        ], 'manager', true, true);

        // ==================== ОТДЕЛ РАЗВИТИЯ - КОНСТРУКТОРА ====================
        // Руководитель конструкторского отдела
        $this->users['dev_constructor_head'] = $this->createUserIfNotExists([
            'last_name' => 'Конструкторов',
            'first_name' => 'Алексей',
            'patronymic' => 'Алексеевич',
            'email' => 'constructor.head@sybox.ru',
            'department_id' => $this->departments['DEV_CONSTRUCTOR']?->id,
            'position_id' => $this->positions['DEV_HEAD']?->id,
        ], 'manager', true, true);

        // Технолог-конструктор
        $this->users['tech_designer'] = $this->createUserIfNotExists([
            'last_name' => 'Сидоров',
            'first_name' => 'Сидор',
            'patronymic' => 'Сидорович',
            'email' => 'tech.designer@sybox.ru',
            'department_id' => $this->departments['DEV_CONSTRUCTOR']?->id,
            'position_id' => $this->positions['DEV_TECH_DESIGNER']?->id,
        ], 'user', true, true);

        // Ведущий технолог-конструктор
        $this->users['lead_tech_designer'] = $this->createUserIfNotExists([
            'last_name' => 'Кузнецов',
            'first_name' => 'Кузнец',
            'patronymic' => 'Кузнецович',
            'email' => 'lead.tech@sybox.ru',
            'department_id' => $this->departments['DEV_CONSTRUCTOR']?->id,
            'position_id' => $this->positions['DEV_LEAD_TECH_DESIGNER']?->id,
        ], 'user', true, true);

        // ==================== ОТДЕЛ РАЗВИТИЯ - ДИЗАЙНЕРЫ ====================
        // Руководитель дизайн-отдела
        $this->users['dev_design_head'] = $this->createUserIfNotExists([
            'last_name' => 'Дизайнеров',
            'first_name' => 'Анна',
            'patronymic' => 'Викторовна',
            'email' => 'design.head@sybox.ru',
            'department_id' => $this->departments['DEV_DESIGN']?->id,
            'position_id' => $this->positions['DEV_HEAD']?->id,
        ], 'manager', true, true);

        // Дизайнер
        $this->users['designer'] = $this->createUserIfNotExists([
            'last_name' => 'Васнецова',
            'first_name' => 'Анна',
            'patronymic' => 'Петровна',
            'email' => 'designer@sybox.ru',
            'department_id' => $this->departments['DEV_DESIGN']?->id,
            'position_id' => $this->positions['DEV_DESIGNER']?->id,
        ], 'user', true, true);

        // Ведущий дизайнер
        $this->users['lead_designer'] = $this->createUserIfNotExists([
            'last_name' => 'Репина',
            'first_name' => 'Елена',
            'patronymic' => 'Ивановна',
            'email' => 'lead.designer@sybox.ru',
            'department_id' => $this->departments['DEV_DESIGN']?->id,
            'position_id' => $this->positions['DEV_LEAD_DESIGNER']?->id,
        ], 'user', true, true);

        // ==================== ОТДЕЛ ПРОДАЖ ====================
        $this->users['sales_head'] = $this->createUserIfNotExists([
            'last_name' => 'Смирнов',
            'first_name' => 'Алексей',
            'patronymic' => 'Алексеевич',
            'email' => 'sales.head@sybox.ru',
            'department_id' => $this->departments['SALES']?->id,
            'position_id' => $this->positions['SALES_HEAD']?->id,
        ], 'manager', true, true);

        $this->users['front_manager'] = $this->createUserIfNotExists([
            'last_name' => 'Козлова',
            'first_name' => 'Мария',
            'patronymic' => 'Сергеевна',
            'email' => 'front.manager@sybox.ru',
            'department_id' => $this->departments['SALES']?->id,
            'position_id' => $this->positions['SALES_FRONT']?->id,
        ], 'user', true, true);

        $this->users['back_manager'] = $this->createUserIfNotExists([
            'last_name' => 'Новиков',
            'first_name' => 'Дмитрий',
            'patronymic' => 'Дмитриевич',
            'email' => 'back.manager@sybox.ru',
            'department_id' => $this->departments['SALES']?->id,
            'position_id' => $this->positions['SALES_BACK']?->id,
        ], 'user', true, true);

        // ==================== ТЕСТОВЫЕ ПОЛЬЗОВАТЕЛИ ====================
        $this->users['pending_email'] = $this->createUserIfNotExists([
            'last_name' => 'Ожидающий',
            'first_name' => 'Подтверждение',
            'email' => 'pending-email@yandex.ru',
            'department_id' => $this->departments['DEV_DESIGN']?->id,
            'position_id' => $this->positions['DEV_DESIGNER']?->id,
        ], 'user', false, false);

        $this->users['pending_approval'] = $this->createUserIfNotExists([
            'last_name' => 'Ожидающий',
            'first_name' => 'Одобрение',
            'email' => 'pending@yandex.ru',
            'department_id' => $this->departments['DEV_DESIGN']?->id,
            'position_id' => $this->positions['DEV_DESIGNER']?->id,
        ], 'user', true, false);
    }

    private function createUserIfNotExists(array $data, string $role, bool $verified = true, bool $approved = true): ?User
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            $nickname = User::generateNicknameFromEmail($data['email']);

            $user = User::create([
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'patronymic' => $data['patronymic'] ?? null,
                'nickname' => $nickname,
                'department_id' => $data['department_id'],
                'position_id' => $data['position_id'],
                'email' => $data['email'],
                'password' => bcrypt('password'),
                'email_verified_at' => $verified ? now() : null,
                'approved_at' => $approved ? now() : null,
            ]);

            $user->assignRole($role);
            $this->command->info("   👤 User created: {$data['email']} ({$role})");
        } else {
            $this->command->info("   👤 User already exists: {$data['email']}");
        }

        return $user;
    }

    private function displayUsersInfo(): void
    {
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📋 AVAILABLE USERS (password: password)');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('ADMINISTRATOR:');
        $this->command->info('  • admin@gofralub.ru (Admin, IT Head)');
        $this->command->info('');
        $this->command->info('IT DEPARTMENT:');
        $this->command->info('  • it.head@sybox.ru (User, IT Head)');
        $this->command->info('');
        $this->command->info('DEVELOPMENT DEPARTMENT (Parent):');
        $this->command->info('  • dev.head@sybox.ru (Manager, Head of Development Department)');
        $this->command->info('');
        $this->command->info('DEVELOPMENT DEPARTMENT - CONSTRUCTORS:');
        $this->command->info('  • constructor.head@sybox.ru (Manager, Head of Constructors)');
        $this->command->info('  • tech.designer@sybox.ru (User, Technologist-Designer)');
        $this->command->info('  • lead.tech@sybox.ru (User, Lead Technologist-Designer)');
        $this->command->info('');
        $this->command->info('DEVELOPMENT DEPARTMENT - DESIGNERS:');
        $this->command->info('  • design.head@sybox.ru (Manager, Head of Designers)');
        $this->command->info('  • designer@sybox.ru (User, Designer)');
        $this->command->info('  • lead.designer@sybox.ru (User, Lead Designer)');
        $this->command->info('');
        $this->command->info('SALES DEPARTMENT:');
        $this->command->info('  • sales.head@sybox.ru (Manager, Sales Head)');
        $this->command->info('  • front.manager@sybox.ru (User, Front Manager)');
        $this->command->info('  • back.manager@sybox.ru (User, Back Manager)');
        $this->command->info('');
        $this->command->info('TEST USERS:');
        $this->command->info('  • pending-email@yandex.ru (email NOT confirmed)');
        $this->command->info('  • pending@yandex.ru (email confirmed, awaiting approval)');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
