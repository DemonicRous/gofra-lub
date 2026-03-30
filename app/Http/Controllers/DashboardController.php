<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Task;
use App\Models\Audit;
use App\Models\ScoringSheet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Получаем статистику для администратора
        $stats = null;
        if ($user->hasRole('admin')) {
            $totalUsers = User::count();
            $activeUsers = User::whereNotNull('approved_at')->count();
            $pendingUsers = User::whereNull('approved_at')->whereNotNull('email_verified_at')->count();
            $verifiedUsers = User::whereNotNull('email_verified_at')->count();

            $totalDepartments = Department::count();
            $totalPositions = Position::count();

            $activePercentage = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0;

            $stats = [
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers,
                'pendingUsers' => $pendingUsers,
                'verifiedUsers' => $verifiedUsers,
                'totalDepartments' => $totalDepartments,
                'totalPositions' => $totalPositions,
                'activePercentage' => $activePercentage,
            ];
        }

        // Статистика по задачам
        $tasks = Task::visibleTo($user)->get();
        $taskStats = [
            'active' => $tasks->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'total' => $tasks->count(),
        ];

        // Статистика по аудитам
        $audits = Audit::visibleTo($user)->get();
        $auditStats = [
            'active' => $audits->where('status', 'in_progress')->count(),
            'total' => $audits->count(),
        ];

        // Статистика по баллам
        $currentMonth = Carbon::now()->startOfMonth();
        $sheet = ScoringSheet::where('user_id', $user->id)
            ->where('period_date', $currentMonth)
            ->first();

        $scoringStats = [
            'currentMonthPoints' => $sheet ? $sheet->total_points : 0,
            'hasSheet' => (bool) $sheet,
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'taskStats' => $taskStats,
            'auditStats' => $auditStats,
            'scoringStats' => $scoringStats,
            'user' => [ // Явно передаем пользователя для отладки
                'email_verified_at' => $user->email_verified_at,
                'approved_at' => $user->approved_at,
                'created_at' => $user->created_at,
            ]
        ]);
    }
}
