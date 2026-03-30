<?php
// database/migrations/0001_01_01_0000001_create_initial_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ==================== ОСНОВНЫЕ ТАБЛИЦЫ ====================

        // 1. Создаем users без внешних ключей
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('last_name');
                $table->string('first_name');
                $table->string('patronymic')->nullable();
                $table->string('nickname')->unique();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->timestamp('approved_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // 2. Создаем departments
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->unique()->nullable();
                $table->text('description')->nullable();
                $table->foreignId('head_id')->nullable();
                $table->foreignId('parent_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Создаем positions
        if (!Schema::hasTable('positions')) {
            Schema::create('positions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->unique()->nullable();
                $table->text('description')->nullable();
                $table->foreignId('department_id')
                    ->constrained('departments')
                    ->cascadeOnDelete();
                $table->enum('level', ['junior', 'middle', 'senior', 'lead', 'head'])
                    ->default('middle');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Добавляем внешние ключи к users
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('nickname')
                    ->constrained('departments')
                    ->nullOnDelete();
                $table->foreignId('position_id')
                    ->nullable()
                    ->after('department_id')
                    ->constrained('positions')
                    ->nullOnDelete();
                // Добавляем поле для подотдела системы подсчета баллов
                $table->enum('scoring_department', ['constructor', 'designer'])
                    ->nullable()
                    ->after('position_id');
            });
        }

        // 5. Добавляем внешние ключи к departments
        if (Schema::hasTable('departments')) {
            // Внешний ключ для head_id
            $headForeignKey = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = 'departments'
                AND COLUMN_NAME = 'head_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [config('database.connections.mysql.database')]);

            if (empty($headForeignKey)) {
                Schema::table('departments', function (Blueprint $table) {
                    $table->foreign('head_id')
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();
                });
            }

            // Внешний ключ для parent_id
            $parentForeignKey = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = 'departments'
                AND COLUMN_NAME = 'parent_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [config('database.connections.mysql.database')]);

            if (empty($parentForeignKey)) {
                Schema::table('departments', function (Blueprint $table) {
                    $table->foreign('parent_id')
                        ->references('id')
                        ->on('departments')
                        ->nullOnDelete();
                });
            }
        }

        // ==================== НОВАЯ ОПТИМИЗИРОВАННАЯ СИСТЕМА ЗАДАЧ ====================

        // 6. Удаляем старые таблицы задач, если они существуют
        Schema::dropIfExists('todo_participants');
        Schema::dropIfExists('todo_subtasks');
        Schema::dropIfExists('todo_comments');
        Schema::dropIfExists('todos');

        // 7. Создаем таблицу проектов
        if (!Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('color')->default('#3B82F6');
                $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('owner_id');
                $table->index('name');
            });
        }

        // 8. Создаем оптимизированную таблицу задач
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();

                // Основные поля
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('type')->default('task');
                $table->string('status')->default('todo');
                $table->string('priority')->default('medium');

                // Видимость и права доступа
                $table->string('visibility')->default('personal');
                $table->json('allowed_roles')->nullable();
                $table->json('allowed_departments')->nullable();

                // Сроки
                $table->timestamp('due_date')->nullable();
                $table->timestamp('reminder_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();

                // Повторяющиеся задачи
                $table->string('recurrence_pattern')->nullable();
                $table->json('recurrence_settings')->nullable();
                $table->unsignedBigInteger('parent_task_id')->nullable();

                // Прогресс
                $table->unsignedTinyInteger('progress')->default(0);
                $table->json('metadata')->nullable();

                // Связи
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();

                // Оптимизированные индексы для быстрого поиска
                $table->index(['status', 'priority', 'due_date']);
                $table->index(['visibility', 'created_by']);
                $table->index(['assigned_to', 'status']);
                $table->index(['created_by', 'status']);
                $table->index(['department_id', 'visibility']);
                $table->index('uuid');
                $table->index('due_date');
                $table->index('reminder_at');

                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 9. Добавляем само-ссылку для parent_task_id
        if (Schema::hasTable('tasks') && !Schema::hasColumn('tasks', 'parent_task_id')) {
            $parentTaskForeignKey = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = 'tasks'
                AND COLUMN_NAME = 'parent_task_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [config('database.connections.mysql.database')]);

            if (empty($parentTaskForeignKey)) {
                Schema::table('tasks', function (Blueprint $table) {
                    $table->foreign('parent_task_id')
                        ->references('id')
                        ->on('tasks')
                        ->nullOnDelete();
                });
            }
        }

        // 10. Таблица участников задач
        if (!Schema::hasTable('task_participants')) {
            Schema::create('task_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role')->default('participant'); // participant, reviewer, observer
                $table->json('permissions')->nullable();
                $table->timestamps();

                $table->unique(['task_id', 'user_id']);
                $table->index(['user_id', 'role']);
                $table->index('task_id');
            });
        }

        // 11. Таблица подзадач
        if (!Schema::hasTable('task_subtasks')) {
            Schema::create('task_subtasks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->boolean('is_completed')->default(false);
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index(['task_id', 'is_completed']);
                $table->index('task_id');
            });
        }

        // 12. Таблица комментариев
        if (!Schema::hasTable('task_comments')) {
            Schema::create('task_comments', function (Blueprint $table) {
                $table->id();
                $table->text('content');
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->json('mentions')->nullable();
                $table->timestamps();

                $table->index(['task_id', 'created_at']);
                $table->index('user_id');
            });
        }

        // 13. Таблица истории изменений задач
        if (!Schema::hasTable('task_histories')) {
            Schema::create('task_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('field');
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->timestamps();

                $table->index(['task_id', 'created_at']);
                $table->index('user_id');
            });
        }

        // 14. Таблица тегов
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('color')->default('#3B82F6');
                $table->timestamps();

                $table->index('name');
            });
        }

        // 15. Связующая таблица задач и тегов
        if (!Schema::hasTable('task_tag')) {
            Schema::create('task_tag', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['task_id', 'tag_id']);
                $table->index('task_id');
                $table->index('tag_id');
            });
        }

        // 16. Таблица участников проектов
        if (!Schema::hasTable('project_members')) {
            Schema::create('project_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role')->default('member');
                $table->timestamps();

                $table->unique(['project_id', 'user_id']);
                $table->index(['user_id', 'role']);
            });
        }

        // ==================== СИСТЕМА ПОДСЧЕТА БАЛЛОВ ====================

        // 17. Таблица категорий баллов (с базовыми баллами)
        if (!Schema::hasTable('scoring_categories')) {
            Schema::create('scoring_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('type', ['constructor', 'designer', 'common'])->default('constructor');
                $table->decimal('base_points', 8, 2)->default(0)->comment('Базовые баллы за категорию');
                $table->decimal('points', 8, 2)->default(0)->comment('Дополнительные баллы за подкатегорию');
                $table->string('unit')->default('шт');
                $table->foreignId('parent_id')->nullable()->constrained('scoring_categories')->nullOnDelete();
                $table->boolean('is_multiselect')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['type', 'is_active']);
                $table->index('parent_id');
            });
        }

        // 18. Таблица ведомостей
        if (!Schema::hasTable('scoring_sheets')) {
            Schema::create('scoring_sheets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('period_date');
                $table->enum('status', ['draft', 'confirmed', 'approved'])->default('draft');
                $table->decimal('total_points', 10, 2)->default(0);
                $table->timestamp('confirmed_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'period_date']);
                $table->index(['status', 'period_date']);
                $table->index('user_id');
            });
        }

        // 19. Таблица записей выполненных работ (с metadata)
        if (!Schema::hasTable('scoring_entries')) {
            Schema::create('scoring_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sheet_id')->constrained('scoring_sheets')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('scoring_categories')->cascadeOnDelete();
                $table->string('request_number')->nullable();
                $table->string('counterparty')->nullable();
                $table->string('manager_name')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('points', 8, 2)->default(0);
                $table->text('notes')->nullable();
                $table->json('metadata')->nullable()->comment('Хранит выбранные подкатегории и доп. информацию');
                $table->timestamps();

                $table->index('sheet_id');
                $table->index('category_id');
                $table->index('request_number');
            });
        }

        // 20. Таблица вариантов конструкции
        if (!Schema::hasTable('scoring_variants')) {
            Schema::create('scoring_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entry_id')->constrained('scoring_entries')->cascadeOnDelete();
                $table->string('name');
                $table->integer('quantity')->default(1);
                $table->decimal('points', 8, 2)->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('entry_id');
            });
        }

        // ==================== СИСТЕМНЫЕ ТАБЛИЦЫ LARAVEL ====================

        // 21. Таблица для сброса паролей
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // 22. Таблица для сессий
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        // 23. Таблица для кэша
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->bigInteger('expiration')->index();
            });
        }

        if (!Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->bigInteger('expiration')->index();
            });
        }

        // 24. Таблица для очередей
        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (!Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        // ==================== ДОПОЛНИТЕЛЬНЫЕ ОПТИМИЗАЦИИ ====================

        // 25. Добавляем отсутствующие индексы для существующих таблиц
        if (Schema::hasTable('users') && !Schema::hasIndex('users', 'users_email_verified_at_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('email_verified_at');
                $table->index('approved_at');
                $table->index('scoring_department');
            });
        }

        if (Schema::hasTable('departments') && !Schema::hasIndex('departments', 'departments_is_active_index')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->index('is_active');
            });
        }

        if (Schema::hasTable('positions') && !Schema::hasIndex('positions', 'positions_is_active_index')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->index('is_active');
                $table->index('level');
            });
        }

        // ==================== НАЧАЛЬНОЕ НАПОЛНЕНИЕ КАТЕГОРИЙ ====================

        $this->seedCategories();
    }

    /**
     * Начальное наполнение категорий баллов с базовыми баллами
     */
    private function seedCategories(): void
    {
        // Проверяем, есть ли уже категории
        if (\App\Models\ScoringCategory::exists()) {
            return;
        }

        // Категории для конструкторов с базовыми баллами
        $constructorCategories = [
            [
                'name' => 'Конструкция из каталога ФЕФКО',
                'type' => 'constructor',
                'base_points' => 1.0,  // Базовый балл за выбор этой категории
                'is_multiselect' => false,
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Проектирование по размерам, чертежам клиента', 'points' => 0],
                    ['name' => 'Проектирование по образцам продукта клиента', 'points' => 0.5],
                    ['name' => 'Проектирование по образцам упаковки клиента', 'points' => 0.5],
                    ['name' => 'Конструкция из двух элементов крышка-дно', 'points' => 0.5],
                ]
            ],
            [
                'name' => 'Конструкция не из каталога ФЕФКО',
                'type' => 'constructor',
                'base_points' => 3.0,  // Базовый балл за выбор этой категории
                'is_multiselect' => false,
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Проектирование по размерам, чертежам клиента', 'points' => 0],
                    ['name' => 'Проектирование по образцам клиента', 'points' => 0.5],
                    ['name' => 'Проектирование по образцам упаковки клиента', 'points' => 1],
                    ['name' => 'Конструкция из двух элементов. Крышка (ФЕФКО)-дно (НЕТ)', 'points' => 1],
                    ['name' => 'Конструкция из двух элементов. Крышка-дно (НЕ ФЕФКО)', 'points' => 3],
                ]
            ],
            [
                'name' => 'Дополнительные элементы конструкции',
                'type' => 'constructor',
                'base_points' => 0,  // Базовый балл = 0, баллы только за выбранные элементы
                'is_multiselect' => true,
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Добавление решетки RODA', 'points' => 0.5],
                    ['name' => 'Добавление решетки, ложемента. ВЫСЕЧКА', 'points' => 1],
                    ['name' => 'Добавление перфорации', 'points' => 0.5],
                    ['name' => 'Добавление отрывной ленты, самоклеющейся ленты', 'points' => 0.5],
                    ['name' => 'Добавление окошек', 'points' => 0.5],
                    ['name' => 'Оптимизация конструкции упаковки', 'points' => 0.5],
                    ['name' => 'Изменение размеров стандартной (ФЕФКО) упаковки', 'points' => 0.5],
                    ['name' => 'Добавление замочков', 'points' => 0.5],
                ]
            ],
            [
                'name' => 'Специальные категории',
                'type' => 'constructor',
                'base_points' => 0,
                'is_multiselect' => true,
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Новая конструкция, ранее не используемая', 'points' => 6],
                    ['name' => 'Тестирование новых перспективных конструкций', 'points' => 3],
                    ['name' => 'Ламинация, Сшивка, Склейка и тестирование на линиях', 'points' => 1.5],
                    ['name' => 'Вариант на ИнЛайн', 'points' => 0.5],
                ]
            ],
            [
                'name' => 'Командировки',
                'type' => 'constructor',
                'base_points' => 0,
                'is_multiselect' => false,
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Командировка (1/2 дня)', 'points' => 1.5, 'unit' => 'день'],
                    ['name' => 'Командировка (1 день)', 'points' => 3, 'unit' => 'день'],
                ]
            ],
        ];

        // Категории для дизайнеров с базовыми баллами
        $designerCategories = [
            [
                'name' => 'Разработка макетов',
                'type' => 'designer',
                'base_points' => 0,
                'is_multiselect' => true,
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Разработка макета (стандартный)', 'points' => 2],
                    ['name' => 'Редизайн', 'points' => 1.5],
                    ['name' => 'Адаптация под разные форматы', 'points' => 1],
                    ['name' => 'Сложный макет (многостраничный)', 'points' => 4],
                ]
            ],
            [
                'name' => 'Печатная продукция',
                'type' => 'designer',
                'base_points' => 0,
                'is_multiselect' => true,
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Разработка этикетки', 'points' => 3],
                    ['name' => 'Разработка упаковки', 'points' => 2],
                    ['name' => 'Брошюра/каталог', 'points' => 4],
                    ['name' => 'Плакат/баннер', 'points' => 1.5],
                ]
            ],
            [
                'name' => 'Цифровой дизайн',
                'type' => 'designer',
                'base_points' => 0,
                'is_multiselect' => true,
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Баннеры для сайта', 'points' => 1.5],
                    ['name' => 'Презентация', 'points' => 2],
                    ['name' => 'Дизайн сайта (страница)', 'points' => 5],
                    ['name' => 'Инфографика', 'points' => 2.5],
                ]
            ],
            [
                'name' => 'Фото/Видео',
                'type' => 'designer',
                'base_points' => 0,
                'is_multiselect' => true,
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Фотосъемка продукции', 'points' => 3],
                    ['name' => 'Видеосъемка/монтаж', 'points' => 4],
                    ['name' => 'Ретушь фотографий', 'points' => 1],
                ]
            ],
        ];

        // Создаем категории для конструкторов
        foreach ($constructorCategories as $parentData) {
            $parent = \App\Models\ScoringCategory::create([
                'name' => $parentData['name'],
                'type' => $parentData['type'],
                'base_points' => $parentData['base_points'],
                'is_multiselect' => $parentData['is_multiselect'],
                'sort_order' => $parentData['sort_order'],
                'is_active' => true,
            ]);

            foreach ($parentData['children'] as $child) {
                \App\Models\ScoringCategory::create([
                    'name' => $child['name'],
                    'type' => $parentData['type'],
                    'points' => $child['points'],
                    'unit' => $child['unit'] ?? 'шт',
                    'parent_id' => $parent->id,
                    'is_active' => true,
                ]);
            }
        }

        // Создаем категории для дизайнеров
        foreach ($designerCategories as $parentData) {
            $parent = \App\Models\ScoringCategory::create([
                'name' => $parentData['name'],
                'type' => $parentData['type'],
                'base_points' => $parentData['base_points'],
                'is_multiselect' => $parentData['is_multiselect'],
                'sort_order' => $parentData['sort_order'],
                'is_active' => true,
            ]);

            foreach ($parentData['children'] as $child) {
                \App\Models\ScoringCategory::create([
                    'name' => $child['name'],
                    'type' => $parentData['type'],
                    'points' => $child['points'],
                    'unit' => $child['unit'] ?? 'шт',
                    'parent_id' => $parent->id,
                    'is_active' => true,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Отключаем проверку внешних ключей для безопасного удаления
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Удаляем таблицы системы подсчета баллов
        Schema::dropIfExists('scoring_variants');
        Schema::dropIfExists('scoring_entries');
        Schema::dropIfExists('scoring_sheets');
        Schema::dropIfExists('scoring_categories');

        // Удаляем новые таблицы задач в обратном порядке
        Schema::dropIfExists('task_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('task_histories');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_subtasks');
        Schema::dropIfExists('task_participants');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('projects');

        // Удаляем старые таблицы задач
        Schema::dropIfExists('todo_participants');
        Schema::dropIfExists('todo_subtasks');
        Schema::dropIfExists('todo_comments');
        Schema::dropIfExists('todos');

        // Удаляем системные таблицы
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');

        // Удаляем поле из users
        if (Schema::hasColumn('users', 'scoring_department')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('scoring_department');
            });
        }

        // Удаляем основные таблицы
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('users');

        // Включаем проверку внешних ключей обратно
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
