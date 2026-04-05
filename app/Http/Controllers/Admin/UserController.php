<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Notifications\AccountApproved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Отображение списка пользователей
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $query = User::query();

        switch ($status) {
            case 'pending':
                $query->whereNull('approved_at')->whereNotNull('email_verified_at');
                break;
            case 'approved':
                $query->whereNotNull('approved_at');
                break;
        }

        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('patronymic', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nickname', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortFields = ['created_at', 'last_name', 'first_name', 'email', 'nickname'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->with(['roles', 'department', 'position'])
            ->select([
                'id', 'last_name', 'first_name', 'patronymic', 'nickname', 'email',
                'department_id', 'position_id', 'scoring_department',
                'approved_at', 'email_verified_at', 'created_at', 'updated_at'
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
                    'position_name' => $user->position?->name,
                    'position_id' => $user->position_id,
                    'department_name' => $user->department?->name,
                    'department_id' => $user->department_id,
                    'scoring_department' => $user->scoring_department,
                    'role' => $user->roles->first()?->name ?? 'user',
                    'approved_at' => $user->approved_at,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ];
            });

        $stats = [
            'total' => User::count(),
            'pending' => User::whereNull('approved_at')->whereNotNull('email_verified_at')->count(),
            'approved' => User::whereNotNull('approved_at')->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
        ];

        $departments = Department::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'search' => $request->get('search'),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
            'departments' => $departments
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

        // Отправляем уведомление об одобрении
        $user->notify(new AccountApproved());

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
        $user = User::with(['roles', 'department', 'position'])->findOrFail($id);

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $allPositions = Position::where('is_active', true)
            ->with('department')
            ->orderBy('name')
            ->get();

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
                'position_name' => $user->position?->name,
                'position_id' => $user->position_id,
                'position_level' => $user->position?->level,
                'department_name' => $user->department?->name,
                'department_id' => $user->department_id,
                'scoring_department' => $user->scoring_department,
                'role' => $user->roles->first()?->name ?? 'user',
                'roles' => $user->getRoleNames(),
                'approved_at' => $user->approved_at,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'departments' => $departments,
            'allPositions' => $allPositions
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
            'nickname' => 'required|string|max:255|unique:users,nickname,' . $id,
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'email' => 'required|email|unique:users,email,' . $id,
            'scoring_department' => 'nullable|in:constructor,designer',
        ]);

        $user->update($validated);

        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->back()->with('success', 'Данные пользователя обновлены.');
    }

    /**
     * Массовое назначение подотдела (scoring_department) для пользователей отдела
     */
    public function assignScoringDepartment(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'scoring_department' => 'required|in:constructor,designer',
        ]);

        $departmentId = $request->department_id;
        $scoringDepartment = $request->scoring_department;

        // Обновляем только одобренных пользователей, у которых ещё не назначен подотдел или он отличается
        $updatedCount = User::where('department_id', $departmentId)
            ->whereNotNull('approved_at')
            ->where(function ($query) use ($scoringDepartment) {
                $query->whereNull('scoring_department')
                    ->orWhere('scoring_department', '!=', $scoringDepartment);
            })
            ->update(['scoring_department' => $scoringDepartment]);

        // Создаём ведомости для обновлённых пользователей (если ещё нет за текущий месяц)
        $users = User::where('department_id', $departmentId)
            ->whereNotNull('approved_at')
            ->where('scoring_department', $scoringDepartment)
            ->get();

        $sheetService = app(\App\Services\Scoring\SheetService::class);
        $createdCount = 0;
        $currentMonth = now()->startOfMonth();

        foreach ($users as $user) {
            // Проверяем, существует ли уже ведомость за текущий месяц
            $exists = \App\Models\ScoringSheet::where('user_id', $user->id)
                ->where('period_date', $currentMonth)
                ->exists();

            if (!$exists) {
                $sheetService->createSheetForUser($user, $currentMonth);
                $createdCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Обновлено пользователей: {$updatedCount}. Создано ведомостей: {$createdCount}.",
            'updated' => $updatedCount,
            'created' => $createdCount,
        ]);
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
            'by_department' => $this->getDepartmentStatistics(),
            'recent' => $this->getRecentUsers(),
        ];

        return Inertia::render('Admin/Statistics', ['stats' => $stats]);
    }

    /**
     * Получить статистику по отделам
     */
    private function getDepartmentStatistics()
    {
        // Получаем количество пользователей по отделам
        $departmentStats = DB::table('users')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->whereNotNull('users.approved_at')
            ->select('departments.name as department_name', DB::raw('count(*) as count'))
            ->groupBy('departments.id', 'departments.name')
            ->get();

        // Преобразуем в массив вида [название_отдела => количество]
        $result = [];
        foreach ($departmentStats as $stat) {
            $result[$stat->department_name] = $stat->count;
        }

        // Добавляем отделы без сотрудников
        $departmentsWithoutUsers = Department::whereDoesntHave('users', function($query) {
            $query->whereNotNull('approved_at');
        })->get();

        foreach ($departmentsWithoutUsers as $dept) {
            $result[$dept->name] = 0;
        }

        // Сортируем по названию
        ksort($result);

        return $result;
    }

    /**
     * Получить последних зарегистрированных пользователей
     */
    private function getRecentUsers()
    {
        return User::with(['department', 'position'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'position_name' => $user->position?->name,
                    'department_name' => $user->department?->name,
                    'created_at' => $user->created_at,
                ];
            });
    }

    /**
     * Получение списка пользователей для выбора руководителя
     * Только пользователи с ролью lead или head, подтвержденным email и одобренные
     */
    public function getLeaders(Request $request)
    {
        // Получаем ID должностей с уровнем head или lead
        $leaderPositionIds = Position::whereIn('level', ['head', 'lead'])
            ->where('is_active', true)
            ->pluck('id');

        $users = User::with(['department', 'position'])
            ->whereNotNull('email_verified_at')
            ->whereNotNull('approved_at')
            ->whereIn('position_id', $leaderPositionIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'last_name', 'first_name', 'patronymic', 'position_id', 'department_id']);

        // Форматируем данные для удобного отображения
        $formattedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'short_name' => $user->short_name,
                'position_name' => $user->position?->name,
                'position_level' => $user->position?->level,
                'department_name' => $user->department?->name,
            ];
        });

        return response()->json([
            'success' => true,
            'users' => $formattedUsers
        ]);
    }

}
