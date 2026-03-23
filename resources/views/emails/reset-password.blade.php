@extends('emails.layout')

@section('content')
    <h1>Сброс пароля</h1>

    <p>Здравствуйте, <strong>{{ $user->full_name ?? $user->email }}</strong>!</p>

    <p>Мы получили запрос на сброс пароля для вашей учетной записи в системе <strong>GofraLub</strong>.</p>

    <div class="info-box">
        <p>🔐 Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо. Ваш пароль останется без изменений.</p>
    </div>

    <div class="text-center">
        <a href="{{ $url }}" class="email-button">Сбросить пароль</a>
    </div>

    <p style="font-size: 14px; color: #6b7280; text-align: center; margin-top: 16px;">
        Или скопируйте ссылку в браузер:<br>
        <span style="word-break: break-all; color: #3b82f6;">{{ $url }}</span>
    </p>

    <div class="divider"></div>

    <p style="font-size: 14px;">
        <span class="badge">Важно</span>
    </p>

    <p style="font-size: 14px;">
        Ссылка для сброса пароля действительна в течение 60 минут.<br>
        После истечения срока действия, вам потребуется запросить сброс пароля повторно.
    </p>

    <p style="font-size: 14px; margin-top: 24px;">
        С уважением,<br>
        <strong>Команда GofraLub</strong>
    </p>
@endsection
