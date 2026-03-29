<?php
// database/migrations/2026_03_29_000001_create_audits_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Таблица аудитов
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Основная информация
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('findings')->nullable(); // Выявленные проблемы/замечания
            $table->text('recommendations')->nullable(); // Рекомендации

            // Адрес и клиент
            $table->string('client_name')->nullable();
            $table->string('client_contact')->nullable();
            $table->string('address')->nullable();
            $table->string('object_name')->nullable(); // Название объекта/оборудования

            // Статус аудита
            $table->enum('status', [
                'draft',           // Черновик
                'in_progress',     // В процессе
                'completed',       // Завершен
                'cancelled'        // Отменен
            ])->default('draft');

            // Тип аудита
            $table->enum('audit_type', [
                'measurement',     // Замеры параметров
                'production_line', // Изучение производственной линии
                'quality_check',   // Проверка качества
                'consultation',    // Консультация
                'other'            // Другое
            ])->default('measurement');

            // Даты
            $table->date('audit_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Координаты (для геолокации)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Подписи
            $table->text('signature_data')->nullable(); // Данные подписи (base64)
            $table->timestamp('signed_at')->nullable();

            // Связи
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('related_task_id')->nullable()->constrained('tasks')->nullOnDelete(); // Связь с задачей

            $table->timestamps();
            $table->softDeletes();

            // Индексы для оптимизации
            $table->index(['status', 'audit_date']);
            $table->index(['created_by', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('audit_date');
        });

        // Таблица медиафайлов для аудитов
        Schema::create('audit_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('audits')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();

            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->unsignedBigInteger('size');

            $table->enum('media_type', [
                'photo',      // Фотография
                'video',      // Видео
                'document',   // Документ
                'drawing',    // Чертеж
                'other'       // Другое
            ])->default('photo');

            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // EXIF данные, GPS координаты и т.д.

            $table->boolean('is_public')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['audit_id', 'media_type']);
            $table->index('uploaded_by');
        });

        // Таблица комментариев к аудиту
        Schema::create('audit_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('audits')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->text('content');
            $table->json('mentions')->nullable(); // Упоминания пользователей
            $table->json('attachments')->nullable(); // ID прикрепленных файлов

            $table->timestamps();

            $table->index(['audit_id', 'created_at']);
            $table->index('user_id');
        });

        // Таблица шаблонов аудитов
        Schema::create('audit_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('sections')->nullable(); // Структура шаблона (секции, вопросы)
            $table->json('checklist')->nullable(); // Чек-лист для проверки
            $table->enum('audit_type', [
                'measurement', 'production_line', 'quality_check', 'consultation', 'other'
            ])->default('measurement');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_templates');
        Schema::dropIfExists('audit_comments');
        Schema::dropIfExists('audit_media');
        Schema::dropIfExists('audits');
    }
};
