<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('approved_at', null)
            ->whereNotNull('email_verified_at')
            ->get(['id', 'name', 'email', 'created_at']);

        return Inertia::render('Admin/Users', ['users' => $users]);
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->approved_at = now();
        $user->assignRole('user'); // назначаем роль обычного пользователя
        $user->save();

        return redirect()->back()->with('success', 'Пользователь одобрен.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Пользователь удален.');
    }
}
