{{-- resources/views/exports/audit-pdf.blade.php --}}
    <!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Аудит: {{ $audit->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 15px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #3B82F6;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 11px;
            color: #666;
        }

        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Табличная верстка для информации */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .info-label {
            width: 35%;
            font-weight: bold;
            color: #6b7280;
        }

        .info-value {
            width: 65%;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        .status-draft { background-color: #9CA3AF; }
        .status-in_progress { background-color: #F59E0B; }
        .status-completed { background-color: #10B981; }
        .status-cancelled { background-color: #EF4444; }

        .content-text {
            background-color: #f8fafc;
            padding: 8px;
            border-radius: 6px;
            margin-top: 5px;
            font-size: 11px;
        }

        /* Сетка для фотографий */
        .photo-grid {
            width: 100%;
            margin-top: 10px;
        }

        .photo-row {
            width: 100%;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .photo-cell {
            float: left;
            width: 33.33%;
            padding: 0 5px;
            box-sizing: border-box;
        }

        .photo-frame {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 6px;
            background-color: #f9fafb;
            text-align: center;
        }

        .photo-container {
            width: 100%;
            height: 160px;
            overflow: hidden;
            border-radius: 4px;
            background-color: #e5e7eb;
        }

        .photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .photo-caption {
            font-size: 8px;
            color: #6b7280;
            margin-top: 6px;
            word-break: break-word;
        }

        .photo-name {
            font-size: 7px;
            color: #9ca3af;
            margin-top: 2px;
            word-break: break-word;
        }

        /* Очистка float */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .comment {
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #e5e7eb;
        }

        .comment-author {
            font-weight: bold;
            color: #2563eb;
            font-size: 11px;
        }

        .comment-date {
            font-size: 9px;
            color: #94a3b8;
            margin-left: 8px;
        }

        .comment-content {
            margin-top: 4px;
            margin-left: 8px;
            font-size: 10px;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }

        .alert-box {
            padding: 8px;
            border-radius: 6px;
            margin: 10px 0;
            font-size: 11px;
        }

        .alert-box.warning {
            background-color: #fee2e2;
            border-left: 3px solid #ef4444;
        }

        .alert-box.success {
            background-color: #dcfce7;
            border-left: 3px solid #22c55e;
        }

        /* Запрещаем разрыв страницы */
        .section, .photo-cell, .comment {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="title">Отчет по выездному аудиту</div>
    <div class="subtitle">GofraLub - Система управления аудитами | {{ $generated_at }}</div>
</div>

<!-- Основная информация -->
<div class="section">
    <div class="section-title">Основная информация</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Название:</td>
            <td class="info-value"><strong>{{ $audit->title }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">Тип:</td>
            <td class="info-value">{{ $audit->type_name }}</td>
        </tr>
        <tr>
            <td class="info-label">Статус:</td>
            <td class="info-value">
                <span class="status-badge status-{{ $audit->status }}">{{ $audit->status_name }}</span>
            </td>
        </tr>
        <tr>
            <td class="info-label">Дата аудита:</td>
            <td class="info-value">{{ $audit->audit_date ? $audit->audit_date->format('d.m.Y') : 'Не указана' }}</td>
        </tr>
        <tr>
            <td class="info-label">Время проведения:</td>
            <td class="info-value">
                @if($audit->start_time_formatted || $audit->end_time_formatted)
                    {{ $audit->start_time_formatted ?? '—' }} - {{ $audit->end_time_formatted ?? '—' }}
                @else
                    Не указано
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Создатель:</td>
            <td class="info-value">{{ $audit->creator_name }}</td>
        </tr>
        <tr>
            <td class="info-label">Ответственный:</td>
            <td class="info-value">{{ $audit->assignee_name }}</td>
        </tr>
    </table>
</div>

<!-- Информация о клиенте -->
@if($audit->client_name || $audit->address || $audit->client_contact || $audit->object_name)
    <div class="section">
        <div class="section-title">Информация о клиенте</div>
        <table class="info-table">
            @if($audit->client_name)
                <tr>
                    <td class="info-label">Клиент:</td>
                    <td class="info-value">{{ $audit->client_name }}</td>
                </tr>
            @endif
            @if($audit->client_contact)
                <tr>
                    <td class="info-label">Контактное лицо:</td>
                    <td class="info-value">{{ $audit->client_contact }}</td>
                </tr>
            @endif
            @if($audit->address)
                <tr>
                    <td class="info-label">Адрес объекта:</td>
                    <td class="info-value">{{ $audit->address }}</td>
                </tr>
            @endif
            @if($audit->object_name)
                <tr>
                    <td class="info-label">Объект/Оборудование:</td>
                    <td class="info-value">{{ $audit->object_name }}</td>
                </tr>
            @endif
        </table>
    </div>
@endif

<!-- Описание -->
@if($audit->description)
    <div class="section">
        <div class="section-title">Описание</div>
        <div class="content-text">{{ $audit->description }}</div>
    </div>
@endif

<!-- Выявленные проблемы -->
@if($audit->findings)
    <div class="section">
        <div class="section-title">Выявленные проблемы и замечания</div>
        <div class="alert-box warning">{{ $audit->findings }}</div>
    </div>
@endif

<!-- Рекомендации -->
@if($audit->recommendations)
    <div class="section">
        <div class="section-title">Рекомендации</div>
        <div class="alert-box success">{{ $audit->recommendations }}</div>
    </div>
@endif

<!-- Фотоматериалы -->
@php
    $photoCount = count($photos);
@endphp

@if($photoCount > 0)
    <div class="section">
        <div class="section-title">Фотоматериалы ({{ $photoCount }})</div>

        @if($photoCount > 30)
            <div class="alert-box warning" style="background-color:#fee2e2; border-left:3px solid #ef4444; padding:8px; margin-bottom:15px;">
                ⚠️ Внимание: показаны только первые 30 фотографий из {{ $photoCount }} для оптимизации PDF.
            </div>
        @endif

        @for($i = 0; $i < $photoCount; $i += 3)
            <div class="photo-row clearfix">
                @for($j = 0; $j < 3 && $i + $j < $photoCount; $j++)
                    @php $photo = $photos[$i + $j]; @endphp
                    <div class="photo-cell">
                        <div class="photo-frame">
                            <div class="photo-container">
                                <img src="{{ $photo['src'] }}" class="photo" alt="{{ $photo['original_name'] }}">
                            </div>
                            @if($photo['description'])
                                <div class="photo-caption">{{ $photo['description'] }}</div>
                            @endif
                            <div class="photo-name">{{ $photo['original_name'] }}</div>
                        </div>
                    </div>
                @endfor
            </div>
        @endfor
    </div>
@endif

<!-- Комментарии -->
@if($audit->comments->count() > 0)
    <div class="section">
        <div class="section-title">Комментарии ({{ $audit->comments->count() }})</div>
        @foreach($audit->comments as $comment)
            <div class="comment">
                <div>
                    <span class="comment-author">{{ $comment->user?->full_name ?? 'Пользователь' }}</span>
                    <span class="comment-date">{{ $comment->created_at?->format('d.m.Y H:i') }}</span>
                </div>
                <div class="comment-content">{{ $comment->content }}</div>
            </div>
        @endforeach
    </div>
@endif

<div class="footer">
    Отчет сгенерирован автоматически в системе GofraLub | © {{ date('Y') }} GofraLub
</div>
</body>
</html>
