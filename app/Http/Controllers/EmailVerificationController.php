<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Inertia\Inertia;

class EmailVerificationController extends Controller
{
    /**
     * Показать страницу с уведомлением о необходимости подтверждения email
     */
    public function notice(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('dashboard')
            : Inertia::render('Auth/VerifyEmail');
    }

    /**
     * Подтверждение email
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = \App\Models\User::findOrFail($id);

        // Проверяем хеш
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->with('error', 'Неверная ссылка подтверждения.');
        }

        // Проверяем, не подтвержден ли уже email
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('info', 'Email уже подтвержден. Войдите в систему.');
        }

        // Подтверждаем email
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Отправляем уведомление о необходимости одобрения аккаунта
        return redirect()->route('login')->with('success', 'Email успешно подтвержден! Теперь дождитесь одобрения аккаунта администратором.');
    }

    /**
     * Повторная отправка письма с подтверждением
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Ссылка для подтверждения отправлена на ваш email.');
    }
}
