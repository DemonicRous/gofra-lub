<?php

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

    // Новый маршрут для получения руководителей
    Route::get('/leaders', [UserController::class, 'getLeaders'])->name('users.leaders');

    Route::resource('departments', DepartmentController::class);
    Route::resource('positions', PositionController::class);
});

Route::get('/api/positions/by-department/{departmentId}', [PositionController::class, 'getByDepartment']);

// Маршруты для задач (TO-DO) - новая версия
Route::middleware(['auth', 'verified', 'approved'])->prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [App\Http\Controllers\TaskController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\TaskController::class, 'store'])->name('store');
    Route::post('/bulk-update', [App\Http\Controllers\TaskController::class, 'bulkUpdate'])->name('bulk-update');
    Route::get('/export', [App\Http\Controllers\TaskController::class, 'export'])->name('export');

    Route::get('/{task}', [App\Http\Controllers\TaskController::class, 'show'])->name('show');
    Route::put('/{task}', [App\Http\Controllers\TaskController::class, 'update'])->name('update');
    Route::delete('/{task}', [App\Http\Controllers\TaskController::class, 'destroy'])->name('destroy');

    Route::post('/{task}/comments', [App\Http\Controllers\TaskController::class, 'addComment'])->name('comments.store');
    Route::patch('/subtasks/{subtask}', [App\Http\Controllers\TaskController::class, 'toggleSubtask'])->name('subtasks.toggle');
    Route::post('/{task}/subtasks', [App\Http\Controllers\TaskController::class, 'addSubtask'])->name('subtasks.store');

    Route::post('/tasks/{task}/subtasks', [App\Http\Controllers\TaskController::class, 'addSubtask'])->name('tasks.subtasks.store');
    Route::patch('/tasks/subtasks/{subtask}', [App\Http\Controllers\TaskController::class, 'toggleSubtask'])->name('tasks.subtasks.toggle');

    Route::get('/export/excel', [App\Http\Controllers\TaskController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf', [App\Http\Controllers\TaskController::class, 'exportPdf'])->name('export.pdf');

});

// Маршруты для аудитов
Route::middleware(['auth', 'verified', 'approved'])->prefix('audits')->name('audits.')->group(function () {
    Route::get('/', [App\Http\Controllers\AuditController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\AuditController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\AuditController::class, 'store'])->name('store');

    Route::get('/{audit}', [App\Http\Controllers\AuditController::class, 'show'])->name('show');
    Route::put('/{audit}', [App\Http\Controllers\AuditController::class, 'update'])->name('update');
    Route::delete('/{audit}', [App\Http\Controllers\AuditController::class, 'destroy'])->name('destroy');

    Route::post('/{audit}/complete', [App\Http\Controllers\AuditController::class, 'complete'])->name('complete');
    Route::post('/{audit}/media', [App\Http\Controllers\AuditController::class, 'uploadMedia'])->name('media.upload');
    Route::delete('/media/{media}', [App\Http\Controllers\AuditController::class, 'deleteMedia'])->name('media.delete');
    Route::post('/{audit}/comments', [App\Http\Controllers\AuditController::class, 'addComment'])->name('comments.store');
    Route::get('/{audit}/export-pdf', [App\Http\Controllers\AuditController::class, 'exportPdf'])->name('export.pdf');

    Route::post('/{audit}/media/comment', [AuditController::class, 'uploadCommentMedia'])->name('media.upload.comment');
    Route::post('/{audit}/start', [AuditController::class, 'start'])->name('start');
});
