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

        // ==================== СИСТЕМНЫЕ ТАБЛИЦЫ LARAVEL ====================

        // 17. Таблица для сброса паролей
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // 18. Таблица для сессий
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

        // 19. Таблица для кэша
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

        // 20. Таблица для очередей
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

        // 21. Добавляем отсутствующие индексы для существующих таблиц
        if (Schema::hasTable('users') && !Schema::hasIndex('users', 'users_email_verified_at_index')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('email_verified_at');
                $table->index('approved_at');
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
    }

    public function down(): void
    {
        // Отключаем проверку внешних ключей для безопасного удаления
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

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

        // Удаляем основные таблицы
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('users');

        // Включаем проверку внешних ключей обратно
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
