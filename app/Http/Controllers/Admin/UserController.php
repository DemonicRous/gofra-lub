<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Отображение списка пользователей для администрирования
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending'); // pending, approved, all

        $query = User::query();

        // Фильтрация по статусу
        switch ($status) {
            case 'pending':
                $query->whereNull('approved_at')
                    ->whereNotNull('email_verified_at');
                break;
            case 'approved':
                $query->whereNotNull('approved_at');
                break;
            case 'all':
                // без фильтрации
                break;
        }

        // Поиск по имени, email или никнейму
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('patronymic', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nickname', 'like', "%{$search}%");
            });
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->with('roles')
            ->select([
                'id',
                'last_name',
                'first_name',
                'patronymic',
                'nickname',
                'email',
                'position',
                'department',
                'approved_at',
                'email_verified_at',
                'created_at'
            ])
            ->paginate($request->get('per_page', 15))
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'last_name' => $user->last_name,
                    'first_name' => $user->first_name,
                    'patronymic' => $user->patronymic,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'position' => $user->position,
                    'department' => $user->department,
                    'role' => $user->roles->first()?->name ?? 'user',
                    'approved_at' => $user->approved_at,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ];
            });

        // Статистика для админ-панели
        $stats = [
            'total' => User::count(),
            'pending' => User::whereNull('approved_at')->whereNotNull('email_verified_at')->count(),
            'approved' => User::whereNotNull('approved_at')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
        ];

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'search' => $request->get('search'),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    /**
     * Одобрение пользователя
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        if ($user->approved_at !== null) {
            return redirect()->back()->with('error', 'Пользователь уже одобрен.');
        }

        if ($user->email_verified_at === null) {
            return redirect()->back()->with('error', 'Пользователь еще не подтвердил email.');
        }

        $user->approved_at = now();
        $user->save();

        // Назначаем роль пользователя (если еще не назначена)
        if (!$user->hasRole('user')) {
            $user->assignRole('user');
        }

        return redirect()->back()->with('success', "Пользователь {$user->full_name} успешно одобрен.");
    }

    /**
     * Отклонение/удаление пользователя
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $userName = $user->full_name;

        $user->delete();

        return redirect()->back()->with('success', "Пользователь {$userName} удален.");
    }

    /**
     * Назначение роли пользователю
     */
    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name'
        ]);

        $user = User::findOrFail($id);
        $oldRole = $user->roles->first()?->name ?? 'нет';

        // Удаляем все текущие роли и назначаем новую
        $user->syncRoles([$request->role]);

        return redirect()->back()->with('success',
            "Пользователю {$user->full_name} назначена роль: {$request->role} (была: {$oldRole})"
        );
    }

    /**
     * Массовое одобрение пользователей
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $users = User::whereIn('id', $request->user_ids)
            ->whereNull('approved_at')
            ->whereNotNull('email_verified_at')
            ->get();

        $count = 0;
        foreach ($users as $user) {
            $user->approved_at = now();
            $user->save();

            if (!$user->hasRole('user')) {
                $user->assignRole('user');
            }
            $count++;
        }

        return redirect()->back()->with('success', "Одобрено пользователей: {$count}");
    }

    /**
     * Массовое удаление пользователей
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $count = User::whereIn('id', $request->user_ids)->delete();

        return redirect()->back()->with('success', "Удалено пользователей: {$count}");
    }

    /**
     * Детальная информация о пользователе
     */
    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);

        return Inertia::render('Admin/UserShow', [
            'user' => [
                'id' => $user->id,
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'patronymic' => $user->patronymic,
                'full_name' => $user->full_name,
                'short_name' => $user->short_name,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'position' => $user->position,
                'department' => $user->department,
                'role' => $user->roles->first()?->name ?? 'user',
                'roles' => $user->getRoleNames(),
                'approved_at' => $user->approved_at,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Обновление информации о пользователе (админом)
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'nickname' => 'required|string|max:255|unique:users,nickname,' . $id,
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Данные пользователя обновлены.');
    }

    /**
     * Получение статистики для дашборда админа
     */
    public function statistics()
    {
        $stats = [
            'total' => User::count(),
            'approved' => User::whereNotNull('approved_at')->count(),
            'pending' => User::whereNull('approved_at')->whereNotNull('email_verified_at')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'by_role' => [
                'admin' => User::role('admin')->count(),
                'manager' => User::role('manager')->count(),
                'user' => User::role('user')->count(),
            ],
            'by_department' => User::whereNotNull('approved_at')
                ->select('department')
                ->selectRaw('count(*) as count')
                ->groupBy('department')
                ->get()
                ->pluck('count', 'department'),
            'recent' => User::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'last_name', 'first_name', 'email', 'created_at'])
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                    ];
                }),
        ];

        return Inertia::render('Admin/Statistics', ['stats' => $stats]);
    }
}
