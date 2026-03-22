<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            $stats = [
                'totalUsers' => User::count(),
                'activeUsers' => User::whereNotNull('approved_at')->count(),
                'pendingUsers' => User::whereNull('approved_at')->whereNotNull('email_verified_at')->count(),
                'verifiedUsers' => User::whereNotNull('email_verified_at')->count(),
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
