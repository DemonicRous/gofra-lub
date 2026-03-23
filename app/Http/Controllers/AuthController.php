<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Position;
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

    // Обработка входа
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

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
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Получаем все активные должности с информацией об отделе
        $allPositions = Position::where('is_active', true)
            ->with('department')
            ->orderBy('name')
            ->get(['id', 'name', 'level', 'department_id']);

        return Inertia::render('Auth/Register', [
            'departments' => $departments,
            'allPositions' => $allPositions
        ]);
    }

    // Обработка регистрации
    public function register(Request $request)
    {
        $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
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
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Проверяем, что должность принадлежит выбранному отделу
        $position = Position::where('id', $request->position_id)
            ->where('department_id', $request->department_id)
            ->first();

        if (!$position) {
            throw ValidationException::withMessages([
                'position_id' => 'Выбранная должность не принадлежит указанному отделу.',
            ]);
        }

        $nickname = User::generateNicknameFromEmail($request->email);

        $user = User::create([
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'patronymic' => $request->patronymic,
            'nickname' => $nickname,
            'department_id' => $request->department_id,
            'position_id' => $request->position_id,
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
