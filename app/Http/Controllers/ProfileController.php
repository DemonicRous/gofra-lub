<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Показать форму редактирования профиля
     */
    public function edit(Request $request)
    {
        return Inertia::render('Profile/Edit', [
            'user' => $request->user()->only([
                'id', 'last_name', 'first_name', 'patronymic',
                'nickname', 'position', 'department', 'email'
            ]),
        ]);
    }

    /**
     * Обновить профиль
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
                function ($attribute, $value, $fail) {
                    $allowedDomains = ['@sybox.ru', '@uralkarton.ru', '@yandex.ru'];
                    $isValid = false;

                    foreach ($allowedDomains as $domain) {
                        if (str_ends_with($value, $domain)) {
                            $isValid = true;
                            break;
                        }
                    }

                    if (!$isValid) {
                        $fail('Разрешены только email адреса с доменами: @sybox.ru, @uralkarton.ru, @yandex.ru');
                    }
                },
            ],
        ]);

        // Если email изменился, нужно заново подтверждать
        if ($user->email !== $validated['email']) {
            $validated['email_verified_at'] = null;
            $user->sendEmailVerificationNotification();
        }

        $user->update($validated);

        return back()->with('success', 'Профиль успешно обновлен.');
    }

    /**
     * Удалить аккаунт
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Аккаунт успешно удален.');
    }
}
