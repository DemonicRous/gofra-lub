@extends('emails.layout')

@section('content')
    <h1>Аккаунт одобрен! 🎉</h1>

    <p>Здравствуйте, <strong>{{ $user->full_name ?? $user->email }}</strong>!</p>

    <p>Ваша учетная запись в системе <strong>GofraLub</strong> была одобрена администратором.</p>

    <div class="info-box">
        <p>✅ Теперь вы можете войти в систему и начать работу с GofraLub!</p>
    </div>

    <div class="text-center">
        <a href="{{ route('login') }}" class="email-button">Войти в систему</a>
    </div>

    <div class="divider"></div>

    <h2>Что вас ждет в GofraLub?</h2>

    <p>✨ <strong>Управление профилем</strong> — настройте свои личные данные и рабочие параметры</p>
    <p>📊 <strong>Статистика и аналитика</strong> — отслеживайте ключевые показатели отдела развития</p>
    <p>👥 <strong>Командная работа</strong> — взаимодействуйте с коллегами и руководством</p>

    <div class="divider"></div>

    <p style="font-size: 14px;">
        Если у вас возникли вопросы или трудности при работе с системой, обратитесь к администратору или в службу поддержки.
    </p>

    <p style="font-size: 14px; margin-top: 24px;">
        С уважением,<br>
        <strong>Команда GofraLub</strong>
    </p>
@endsection
