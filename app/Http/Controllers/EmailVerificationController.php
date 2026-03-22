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

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->with('error', 'Неверная ссылка подтверждения.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')->with('info', 'Email уже подтвержден.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('dashboard')->with('success', 'Email успешно подтвержден!');
    }

    /**
     * Повторная отправка письма с подтверждением
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Ссылка для подтверждения отправлена на ваш email.');
    }
}
