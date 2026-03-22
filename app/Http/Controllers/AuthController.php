<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    // Отображение формы входа
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    // Обработка входа (поддерживает login по email или nickname)
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Определяем, что ввел пользователь: email или nickname
        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nickname';

        $credentials = [
            $loginField => $request->login,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'login' => 'Пожалуйста, подтвердите email перед входом.',
                ]);
            }

            if (!$user->isApproved()) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'login' => 'Ваш аккаунт ожидает одобрения администратором.',
                ]);
            }

            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'login' => 'Неверные учетные данные.',
        ]);
    }

    // Отображение формы регистрации
    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    // Обработка регистрации
    public function register(Request $request)
    {
        $request->validate([
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
                'unique:users',
                function ($attribute, $value, $fail) {
                    $allowedDomains = ['@sybox.ru', '@uralkarton.ru', '@yandex.ru', '@gmail.com'];
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
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Генерируем никнейм из email
        $nickname = User::generateNicknameFromEmail($request->email);

        $user = User::create([
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'patronymic' => $request->patronymic,
            'nickname' => $nickname,
            'position' => $request->position,
            'department' => $request->department,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);
        return redirect()->route('verification.notice')->with('success', 'Регистрация успешна! Подтвердите email.');
    }

    // Выход
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Вы вышли из системы.');
    }
}
