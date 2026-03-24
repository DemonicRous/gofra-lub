<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

            // Дополнительная статистика для новых функций
            $totalDepartments = Department::count();
            $totalPositions = Position::count();

            // Процент активности системы (пример)
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

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'user' => [ // Явно передаем пользователя для отладки
                'email_verified_at' => $user->email_verified_at,
                'approved_at' => $user->approved_at,
                'created_at' => $user->created_at,
            ]
        ]);
    }
}
