<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
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
                    'position' => $user->position,
                    'department' => $user->department,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'approved_at' => $user->approved_at,
                    'created_at' => $user->created_at,
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
