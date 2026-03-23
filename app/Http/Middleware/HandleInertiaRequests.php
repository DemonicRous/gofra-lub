<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'last_name' => $user->last_name,
                    'first_name' => $user->first_name,
                    'patronymic' => $user->patronymic,
                    'full_name' => $user->full_name,
                    'short_name' => $user->short_name,
                    'nickname' => $user->nickname,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'approved_at' => $user->approved_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    // Получаем название должности и отдела из связей
                    'position_name' => $user->position ? $user->position->name : null,
                    'position_id' => $user->position_id,
                    'position_level' => $user->position ? $user->position->level : null,
                    'department_name' => $user->department ? $user->department->name : null,
                    'department_id' => $user->department_id,
                    'role' => $user->roles->first()?->name ?? 'user',
                    'roles' => $user->getRoleNames(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
