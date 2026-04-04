<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\ScoringCategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Scoring\EntryController;
use App\Http\Controllers\Scoring\ReportController;
use App\Http\Controllers\Scoring\RequestController;
use App\Http\Controllers\Scoring\SheetController;
use App\Http\Controllers\Scoring\VariantController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==================== Публичные страницы ====================
Route::get('/', [HomeController::class, 'index'])->name('pages.home');

// ==================== Аутентификация ====================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Восстановление пароля
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==================== Подтверждение email ====================
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->name('verification.send');
});

// ==================== Защищённые маршруты (требуют авторизации и одобрения) ====================
Route::middleware(['auth', 'approved'])->group(function () {

    // Главная панель
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Профиль
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==================== Задачи ====================
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');

        // Экспорт
        Route::get('/export/excel', [TaskController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [TaskController::class, 'exportPdf'])->name('export.pdf');

        // Комментарии
        Route::post('/{task}/comments', [TaskController::class, 'addComment'])->name('comments.store');

        // Подзадачи
        Route::post('/{task}/subtasks', [TaskController::class, 'addSubtask'])->name('subtasks.store');
        Route::patch('/subtasks/{subtask}', [TaskController::class, 'toggleSubtask'])->name('subtasks.toggle');

        // Массовые операции
        Route::post('/bulk-update', [TaskController::class, 'bulkUpdate'])->name('bulk-update');
    });

    // ==================== Выездные аудиты ====================
    Route::prefix('audits')->name('audits.')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/create', [AuditController::class, 'create'])->name('create');
        Route::post('/', [AuditController::class, 'store'])->name('store');
        Route::get('/{audit}', [AuditController::class, 'show'])->name('show');
        Route::put('/{audit}', [AuditController::class, 'update'])->name('update');
        Route::delete('/{audit}', [AuditController::class, 'destroy'])->name('destroy');

        // Управление аудитом
        Route::post('/{audit}/start', [AuditController::class, 'start'])->name('start');
        Route::post('/{audit}/complete', [AuditController::class, 'complete'])->name('complete');

        // Медиафайлы
        Route::post('/{audit}/media', [AuditController::class, 'uploadMedia'])->name('media.upload');
        Route::delete('/media/{media}', [AuditController::class, 'deleteMedia'])->name('media.delete');
        Route::post('/{audit}/media/comment', [AuditController::class, 'uploadCommentMedia'])->name('media.upload.comment');

        // Комментарии
        Route::post('/{audit}/comments', [AuditController::class, 'addComment'])->name('comments.store');

        // Экспорт PDF
        Route::get('/{audit}/export-pdf', [AuditController::class, 'exportPdf'])->name('export.pdf');
    });

    // ==================== Система баллов (Scoring) ====================
    Route::prefix('scoring')->name('scoring.')->group(function () {
        // Ведомости
        Route::get('/sheets', [SheetController::class, 'index'])->name('index');
        Route::get('/sheets/{sheet}', [SheetController::class, 'show'])->name('sheets.show');
        Route::post('/sheets/{sheet}/confirm', [SheetController::class, 'confirm'])->name('sheets.confirm');

        // Заявки
        Route::post('/sheets/{sheet}/requests', [RequestController::class, 'store'])->name('requests.store');
        Route::put('/requests/{request}', [RequestController::class, 'update'])->name('requests.update');
        Route::delete('/requests/{request}', [RequestController::class, 'destroy'])->name('requests.destroy');

        // Варианты
        Route::put('/variants/{variant}', [VariantController::class, 'update'])->name('variants.update');
        Route::delete('/variants/{variant}', [VariantController::class, 'destroy'])->name('variants.destroy');

        // Записи (Entry) – для добавления вариантов в заявку
        Route::get('/sheets/{sheet}/entries/create', [EntryController::class, 'create'])->name('entries.create');
        Route::post('/sheets/{sheet}/entries', [EntryController::class, 'store'])->name('entries.store');
        Route::delete('/entries/{entry}', [EntryController::class, 'destroy'])->name('entries.destroy');

        // Экспорт
        Route::get('/export/sheet/{sheet}', [ReportController::class, 'exportSheet'])->name('export.sheet');
        Route::get('/export/sheet-pdf/{sheet}', [ReportController::class, 'exportSheetPdf'])->name('export.sheet-pdf');
        Route::get('/export/summary', [ReportController::class, 'exportSummary'])->name('export.summary');

        // Сводка по отделам
        Route::get('/summary', [ReportController::class, 'summary'])->name('summary');
        Route::get('/export/summary', [ReportController::class, 'exportSummary'])->name('export.summary');
        Route::get('/export/sheet/{sheet}', [ReportController::class, 'exportSheet'])->name('export.sheet');
    });

    // ==================== Администрирование (только для администраторов) ====================
    Route::prefix('admin')->name('admin.')->middleware(['admin'])->group(function () {

        // Управление пользователями
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
        Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
        Route::post('/users/bulk-approve', [UserController::class, 'bulkApprove'])->name('users.bulk-approve');
        Route::delete('/users/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
        Route::get('/leaders', [UserController::class, 'getLeaders'])->name('users.leaders');

        // Статистика
        Route::get('/statistics', [UserController::class, 'statistics'])->name('statistics');

        // Управление отделами
        Route::resource('departments', DepartmentController::class)->except(['show']);

        // Управление должностями
        Route::resource('positions', PositionController::class)->except(['show']);
        Route::get('/positions/by-department/{departmentId}', [PositionController::class, 'getByDepartment'])->name('positions.by-department');

        // Управление категориями баллов
        Route::prefix('scoring')->name('scoring.')->group(function () {
            Route::get('/categories', [ScoringCategoryController::class, 'index'])->name('categories');
            Route::post('/categories', [ScoringCategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{category}', [ScoringCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [ScoringCategoryController::class, 'destroy'])->name('categories.destroy');
            Route::post('/categories/reorder', [ScoringCategoryController::class, 'reorder'])->name('categories.reorder');
        });

        // Управление менеджерами (справочник)
        Route::resource('managers', ManagerController::class)->except(['show']);
    });
});

// ==================== Вспомогательные маршруты ====================
// Health check (для мониторинга)
Route::get('/up', function () {
    return response()->json(['status' => 'ok']);
});
