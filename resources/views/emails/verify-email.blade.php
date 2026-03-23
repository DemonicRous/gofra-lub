@extends('emails.layout')

@section('content')

    <style>
        .email-button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            margin: 24px 0;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .email-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
    </style>

    <h1>Подтверждение email адреса</h1>

    <p>Здравствуйте, <strong>{{ $user->full_name ?? $user->email }}</strong>!</p>

    <p>Рады приветствовать вас в <strong>GofraLub</strong> — системе автоматизации работы отдела развития фабрики ЮжУралКартон.</p>

    <div class="info-box">
        <p>📧 Для завершения регистрации и активации аккаунта, пожалуйста, подтвердите ваш email адрес.</p>
    </div>

    <div class="text-center">
        <a href="{{ $url }}" class="email-button">Подтвердить email</a>
    </div>

    <p style="font-size: 14px; color: #6b7280; text-align: center; margin-top: 16px;">
        Или скопируйте ссылку в браузер:<br>
        <span style="word-break: break-all; color: #3b82f6;">{{ $url }}</span>
    </p>

    <div class="divider"></div>

    <p style="font-size: 14px;">
        <span class="badge">Информация</span>
    </p>

    <p style="font-size: 14px;">
        Если вы не регистрировались в GofraLub, просто проигнорируйте это письмо.<br>
        Ссылка для подтверждения действительна в течение 24 часов.
    </p>

    <p style="font-size: 14px; margin-top: 24px;">
        С уважением,<br>
        <strong>Команда GofraLub</strong>
    </p>
@endsection
