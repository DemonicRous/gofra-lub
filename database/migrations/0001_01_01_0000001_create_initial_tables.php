<?php

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

        // ==================== ТАБЛИЦЫ ДЛЯ ЗАДАЧ (TO-DO) ====================

        // 6. Создаем таблицу todos
        if (!Schema::hasTable('todos')) {
            Schema::create('todos', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();

                // Тип задачи - все доступные типы
                $table->enum('type', [
                    'task', 'urgent', 'reminder', 'recurring', 'project', 'idea'
                ])->default('task');

                // Статусы - расширенные для канбан-доски
                $table->enum('status', [
                    'backlog', 'pending', 'in_progress', 'review', 'completed', 'cancelled'
                ])->default('pending');

                // Приоритеты
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

                // Для напоминаний и срочных задач
                $table->timestamp('reminder_at')->nullable();
                $table->boolean('reminder_sent')->default(false);
                $table->timestamp('urgent_notified_at')->nullable();

                // Сроки
                $table->date('due_date')->nullable();
                $table->time('due_time')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('started_at')->nullable();

                // Для повторяющихся задач
                $table->enum('recurring_type', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
                $table->date('recurring_until')->nullable();
                $table->unsignedBigInteger('parent_todo_id')->nullable();

                // Для проектных задач
                $table->unsignedBigInteger('project_id')->nullable();
                $table->integer('progress')->default(0);

                // Видимость
                $table->enum('visibility', ['personal', 'department', 'company'])->default('personal');

                // Связи
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

                $table->timestamps();

                // Индексы
                $table->index(['status', 'assigned_to']);
                $table->index(['visibility', 'department_id']);
                $table->index(['priority', 'due_date']);
                $table->index('reminder_at');
                $table->index('type');
                $table->index('project_id');
                $table->index('parent_todo_id');
            });
        }

        // 7. Добавляем внешние ключи для само-ссылок todos
        if (Schema::hasTable('todos')) {
            // Внешний ключ для parent_todo_id
            $parentTodoForeignKey = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = 'todos'
                AND COLUMN_NAME = 'parent_todo_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [config('database.connections.mysql.database')]);

            if (empty($parentTodoForeignKey)) {
                Schema::table('todos', function (Blueprint $table) {
                    $table->foreign('parent_todo_id')
                        ->references('id')
                        ->on('todos')
                        ->nullOnDelete();
                });
            }

            // Внешний ключ для project_id
            $projectForeignKey = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = 'todos'
                AND COLUMN_NAME = 'project_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [config('database.connections.mysql.database')]);

            if (empty($projectForeignKey)) {
                Schema::table('todos', function (Blueprint $table) {
                    $table->foreign('project_id')
                        ->references('id')
                        ->on('todos')
                        ->nullOnDelete();
                });
            }
        }

        // 8. Таблица для комментариев к задачам
        if (!Schema::hasTable('todo_comments')) {
            Schema::create('todo_comments', function (Blueprint $table) {
                $table->id();
                $table->text('content');
                $table->foreignId('todo_id')->constrained('todos')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // 9. Таблица для подзадач
        if (!Schema::hasTable('todo_subtasks')) {
            Schema::create('todo_subtasks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->boolean('is_completed')->default(false);
                $table->foreignId('todo_id')->constrained('todos')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // 10. Таблица для участников общих задач
        if (!Schema::hasTable('todo_participants')) {
            Schema::create('todo_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('todo_id')->constrained('todos')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['todo_id', 'user_id']);
            });
        }

        // ==================== СИСТЕМНЫЕ ТАБЛИЦЫ LARAVEL ====================

        // 11. Таблица для сброса паролей
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // 12. Таблица для сессий
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

        // 13. Таблица для кэша
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

        // 14. Таблица для очередей
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
    }

    public function down(): void
    {
        // Отключаем проверку внешних ключей для безопасного удаления
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Удаляем таблицы в обратном порядке
        Schema::dropIfExists('todo_participants');
        Schema::dropIfExists('todo_subtasks');
        Schema::dropIfExists('todo_comments');
        Schema::dropIfExists('todos');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('users');

        // Включаем проверку внешних ключей обратно
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
