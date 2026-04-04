<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Ведомость за {{ $sheet->period_date->translatedFormat('F Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #3B82F6;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 13px;
            color: #666;
        }

        /* Карточка информации - отдельные блоки в строку */
        .info-cards {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .info-card {
            flex: 1;
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #e2e8f0;
            min-width: 150px;
        }

        .info-card-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .info-card-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        /* Цветные бейджи для статуса */
        .status-draft {
            background-color: #FEF3C7;
            color: #92400E;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .status-confirmed {
            background-color: #DBEAFE;
            color: #1E40AF;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .status-approved {
            background-color: #D1FAE5;
            color: #065F46;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .total-box {
            background: linear-gradient(135deg, #3B82F6, #2563EB);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
        }

        .total-value {
            font-size: 32px;
            font-weight: bold;
        }

        .total-label {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 5px;
        }

        .request-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .request-header {
            background: #f8fafc;
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .request-title {
            font-size: 15px;
            font-weight: bold;
            color: #1e293b;
        }

        .request-meta {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        .variant-card {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            margin-left: 20px;
        }

        .variant-title {
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .variant-points {
            float: right;
            font-weight: bold;
            color: #3B82F6;
        }

        .entry-item {
            padding: 5px 0 5px 20px;
            font-size: 11px;
            color: #475569;
            border-left: 2px solid #3B82F6;
            margin: 4px 0;
        }

        .entry-points {
            float: right;
            font-weight: 500;
            color: #3B82F6;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .footer {
            margin-top: 25px;
            padding-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="title">Ведомость баллов</div>
    <div class="subtitle">{{ $sheet->period_date->translatedFormat('F Y') }}</div>
    <div class="subtitle">Сформировано: {{ $generated_at }}</div>
</div>

<!-- Три отдельные карточки в строку -->
<div class="info-cards">
    <div class="info-card">
        <div class="info-card-label">СОТРУДНИК</div>
        <div class="info-card-value">{{ $user->full_name ?? '—' }}</div>
    </div>
    <div class="info-card">
        <div class="info-card-label">ПОДОТДЕЛ</div>
        <div class="info-card-value">{{ ($user->scoring_department ?? '') === 'constructor' ? 'Конструкторы' : 'Дизайнеры' }}</div>
    </div>
    <div class="info-card">
        <div class="info-card-label">СТАТУС</div>
        <div class="info-card-value">
            <span class="status-{{ $sheet->status }}">{{ $status_name }}</span>
        </div>
    </div>
    @if($sheet->confirmed_at)
        <div class="info-card">
            <div class="info-card-label">ДАТА ПОДТВЕРЖДЕНИЯ</div>
            <div class="info-card-value">{{ $sheet->confirmed_at->format('d.m.Y H:i') }}</div>
        </div>
    @endif
</div>

<!-- Блок с общей суммой баллов -->
<div class="total-box">
    <div class="total-value">{{ number_format($sheet->total_points, 2, '.', ' ') }}</div>
    <div class="total-label">Всего баллов</div>
</div>

<!-- Список заявок -->
@forelse($requests as $request)
    <div class="request-card">
        <div class="request-header">
            <div class="request-title">Заявка №{{ $request->request_number ?? '—' }}</div>
            <div class="request-meta">
                {{ $request->counterparty ?? 'Контрагент не указан' }} •
                {{ $request->manager_name ?? 'Менеджер не указан' }} •
                {{ $request->created_at->format('d.m.Y H:i') }}
            </div>
        </div>

        @foreach($request->variants as $variant)
            <div class="variant-card">
                <div class="variant-title clearfix">
                    {{ $variant->name }}
                    <span class="variant-points">+{{ number_format($variant->total_points, 2, '.', ' ') }} баллов</span>
                </div>

                @foreach($variant->entries as $entry)
                    <div class="entry-item clearfix">
                        {{ ($entry->category->parent->name ?? '') . ' → ' . ($entry->category->name ?? '') }}
                        <span class="entry-points">+{{ number_format($entry->points, 2, '.', ' ') }}</span>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@empty
    <div style="text-align: center; padding: 40px; color: #94a3b8;">
        Нет заявок в этой ведомости
    </div>
@endforelse

<div class="footer">
    Отчет сгенерирован автоматически в системе GofraLub | © {{ date('Y') }} GofraLub
</div>
</body>
</html>
