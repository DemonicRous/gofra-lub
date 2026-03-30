<?php
// routes/web.php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Публичные маршруты
Route::get('/', [HomeController::class, 'index'])->name('pages.home');

// Маршруты для гостей (не авторизованных)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Маршруты для аутентифицированных пользователей
Route::middleware('auth')->group(function () {
    // Подтверждение email
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware(['throttle:6,1'])->name('verification.send');

    // Выход
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Профиль пользователя (доступен после подтверждения email)
    Route::middleware(['verified'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

// Маршруты для аутентифицированных, подтверждённых и одобренных пользователей
Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Админские маршруты
Route::middleware(['auth', 'verified', 'approved', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{id}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{id}/role', [UserController::class, 'assignRole'])->name('users.assign-role');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('/users/bulk-approve', [UserController::class, 'bulkApprove'])->name('users.bulk-approve');
    Route::delete('/users/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
    Route::get('/statistics', [UserController::class, 'statistics'])->name('statistics');

    // Маршрут для получения руководителей
    Route::get('/leaders', [UserController::class, 'getLeaders'])->name('users.leaders');

    Route::resource('departments', DepartmentController::class);
    Route::resource('positions', PositionController::class);

    // Админские маршруты для управления менеджерами
    Route::prefix('managers')->name('managers.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ManagerController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\ManagerController::class, 'store'])->name('store');
        Route::put('/{manager}', [App\Http\Controllers\Admin\ManagerController::class, 'update'])->name('update');
        Route::delete('/{manager}', [App\Http\Controllers\Admin\ManagerController::class, 'destroy'])->name('destroy');
    });

    // Админские маршруты для категорий баллов
    Route::prefix('scoring')->name('scoring.')->group(function () {
        Route::get('/categories', [App\Http\Controllers\Admin\ScoringCategoryController::class, 'index'])->name('categories');
        Route::post('/categories', [App\Http\Controllers\Admin\ScoringCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [App\Http\Controllers\Admin\ScoringCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [App\Http\Controllers\Admin\ScoringCategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/categories/reorder', [App\Http\Controllers\Admin\ScoringCategoryController::class, 'reorder'])->name('categories.reorder');
    });
});

Route::get('/api/positions/by-department/{departmentId}', [PositionController::class, 'getByDepartment']);

// Маршруты для задач (TO-DO) - новая версия
Route::middleware(['auth', 'verified', 'approved'])->prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::post('/', [TaskController::class, 'store'])->name('store');
    Route::post('/bulk-update', [TaskController::class, 'bulkUpdate'])->name('bulk-update');
    Route::get('/export', [TaskController::class, 'export'])->name('export');

    Route::get('/{task}', [TaskController::class, 'show'])->name('show');
    Route::put('/{task}', [TaskController::class, 'update'])->name('update');
    Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');

    Route::post('/{task}/comments', [TaskController::class, 'addComment'])->name('comments.store');
    Route::patch('/subtasks/{subtask}', [TaskController::class, 'toggleSubtask'])->name('subtasks.toggle');
    Route::post('/{task}/subtasks', [TaskController::class, 'addSubtask'])->name('subtasks.store');

    Route::get('/export/excel', [TaskController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf', [TaskController::class, 'exportPdf'])->name('export.pdf');
});

// Маршруты для аудитов
Route::middleware(['auth', 'verified', 'approved'])->prefix('audits')->name('audits.')->group(function () {
    Route::get('/', [AuditController::class, 'index'])->name('index');
    Route::get('/create', [AuditController::class, 'create'])->name('create');
    Route::post('/', [AuditController::class, 'store'])->name('store');

    Route::get('/{audit}', [AuditController::class, 'show'])->name('show');
    Route::put('/{audit}', [AuditController::class, 'update'])->name('update');
    Route::delete('/{audit}', [AuditController::class, 'destroy'])->name('destroy');

    Route::post('/{audit}/complete', [AuditController::class, 'complete'])->name('complete');
    Route::post('/{audit}/media', [AuditController::class, 'uploadMedia'])->name('media.upload');
    Route::delete('/media/{media}', [AuditController::class, 'deleteMedia'])->name('media.delete');
    Route::post('/{audit}/comments', [AuditController::class, 'addComment'])->name('comments.store');
    Route::get('/{audit}/export-pdf', [AuditController::class, 'exportPdf'])->name('export.pdf');

    Route::post('/{audit}/media/comment', [AuditController::class, 'uploadCommentMedia'])->name('media.upload.comment');
    Route::post('/{audit}/start', [AuditController::class, 'start'])->name('start');
});

// ==================== СИСТЕМА ПОДСЧЕТА БАЛЛОВ ====================

Route::middleware(['auth', 'verified', 'approved'])->prefix('scoring')->name('scoring.')->group(function () {
    // Личные ведомости
    Route::get('/', [App\Http\Controllers\Scoring\SheetController::class, 'index'])->name('index');
    Route::get('/sheets/{sheet}', [App\Http\Controllers\Scoring\SheetController::class, 'show'])->name('sheets.show');
    Route::post('/sheets/{sheet}/confirm', [App\Http\Controllers\Scoring\SheetController::class, 'confirm'])->name('sheets.confirm');

    // Записи
    Route::get('/sheets/{sheet}/entries/create', [App\Http\Controllers\Scoring\EntryController::class, 'create'])->name('entries.create');
    Route::post('/sheets/{sheet}/entries', [App\Http\Controllers\Scoring\EntryController::class, 'store'])->name('entries.store');
    Route::delete('/entries/{entry}', [App\Http\Controllers\Scoring\EntryController::class, 'destroy'])->name('entries.destroy');

    // Отчеты
    Route::get('/summary', [App\Http\Controllers\Scoring\ReportController::class, 'summary'])->name('summary');
    Route::get('/export/sheet/{sheet}', [App\Http\Controllers\Scoring\ReportController::class, 'exportSheet'])->name('export.sheet');
    Route::get('/export/summary', [App\Http\Controllers\Scoring\ReportController::class, 'exportSummary'])->name('export.summary');
});

// API маршруты для Dashboard (опционально)
Route::middleware(['auth', 'verified', 'approved'])->prefix('api/dashboard')->group(function () {
    Route::get('/tasks-stats', function () {
        $user = auth()->user();
        $tasks = \App\Models\Task::visibleTo($user)->get();

        return response()->json([
            'active' => $tasks->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'total' => $tasks->count(),
        ]);
    });

    Route::get('/audits-stats', function () {
        $user = auth()->user();
        $audits = \App\Models\Audit::visibleTo($user)->get();

        return response()->json([
            'active' => $audits->where('status', 'in_progress')->count(),
            'total' => $audits->count(),
        ]);
    });

    Route::get('/scoring-stats', function () {
        $user = auth()->user();
        $currentMonth = \Carbon\Carbon::now()->startOfMonth();

        $sheet = \App\Models\ScoringSheet::where('user_id', $user->id)
            ->where('period_date', $currentMonth)
            ->first();

        return response()->json([
            'current_month_points' => $sheet ? $sheet->total_points : 0,
            'has_sheet' => (bool) $sheet,
        ]);
    });
});
