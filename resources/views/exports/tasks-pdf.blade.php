<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Экспорт задач</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3B82F6;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 15px;
        }

        .stat-card {
            flex: 1;
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3B82F6;
        }

        .stat-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #1e293b;
        }

        .filters {
            background: #f1f5f9;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .filters-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #3B82F6;
        }

        .filter-badge {
            display: inline-block;
            background: white;
            padding: 4px 8px;
            border-radius: 4px;
            margin-right: 8px;
            margin-bottom: 4px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }

        th {
            background: #3B82F6;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }

        .priority-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }

        .type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }

        .page-break {
            page-break-after: always;
        }

        .progress-bar {
            width: 60px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
        }

        .progress-fill {
            height: 100%;
            background: #3B82F6;
            border-radius: 3px;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="title">📊 Отчет по задачам</div>
    <div class="subtitle">GofraLub - Система управления задачами</div>
    <div class="subtitle">Дата формирования: {{ $generated_at }}</div>
</div>

<!-- Статистика -->
<div class="stats">
    <div class="stat-card">
        <div class="stat-label">Всего задач</div>
        <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">В работе</div>
        <div class="stat-value">{{ $stats['by_status']['in_progress'] ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Выполнено</div>
        <div class="stat-value">{{ $stats['by_status']['completed'] ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Просрочено</div>
        <div class="stat-value">{{ $stats['overdue'] ?? 0 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Выполнение</div>
        <div class="stat-value">{{ $stats['completion_rate'] ?? 0 }}%</div>
    </div>
</div>

<!-- Примененные фильтры -->
@if(!empty($filters))
    <div class="filters">
        <div class="filters-title">🔍 Примененные фильтры:</div>
        @if($filters['status'] ?? 'all' != 'all')
            <span class="filter-badge">Статус: {{ $filters['status'] }}</span>
        @endif
        @if($filters['priority'] ?? 'all' != 'all')
            <span class="filter-badge">Приоритет: {{ $filters['priority'] }}</span>
        @endif
        @if($filters['type'] ?? 'all' != 'all')
            <span class="filter-badge">Тип: {{ $filters['type'] }}</span>
        @endif
        @if($filters['search'] ?? '')
            <span class="filter-badge">Поиск: {{ $filters['search'] }}</span>
        @endif
    </div>
@endif

<!-- Таблица задач -->
<table>
    <thead>
    <tr>
        <th>ID</th>
        <th style="width: 20%">Название</th>
        <th>Статус</th>
        <th>Приоритет</th>
        <th>Тип</th>
        <th>Видимость</th>
        <th>Создатель</th>
        <th>Ответственный</th>
        <th>Срок</th>
        <th>Прогресс</th>
        <th>Создана</th>
    </tr>
    </thead>
    <tbody>
    @forelse($tasks as $task)
        <tr>
            <td>{{ $task->id }}</td>
            <td>
                <strong>{{ $task->title }}</strong>
                @if($task->description)
                    <div style="font-size: 9px; color: #666; margin-top: 3px;">{{ Str::limit($task->description, 60) }}</div>
                @endif
            </td>
            <td>
                    <span class="status-badge" style="background: {{ $task->status == 'backlog' ? '#6B7280' : ($task->status == 'todo' ? '#3B82F6' : ($task->status == 'in_progress' ? '#F59E0B' : ($task->status == 'in_review' ? '#8B5CF6' : ($task->status == 'completed' ? '#10B981' : '#EF4444')))) }}">
                        {{ $task->status_name ?? $task->status }}
                    </span>
            </td>
            <td>
                    <span class="priority-badge" style="background: {{ $task->priority == 'low' ? '#6B7280' : ($task->priority == 'medium' ? '#3B82F6' : ($task->priority == 'high' ? '#F59E0B' : ($task->priority == 'urgent' ? '#EF4444' : '#8B5CF6'))) }}">
                        {{ $task->priority_name ?? $task->priority }}
                    </span>
            </td>
            <td>
                    <span class="type-badge" style="background: {{ $task->type == 'urgent' ? '#FEE2E2' : ($task->type == 'reminder' ? '#F3E8FF' : ($task->type == 'idea' ? '#FEF3C7' : '#E0E7FF')) }}; color: #374151;">
                        {{ $task->type_name ?? $task->type }}
                    </span>
            </td>
            <td>{{ $task->visibility_name ?? $task->visibility }}</td>
            <td>{{ $task->creator ? $task->creator->short_name : '—' }}</td>
            <td>{{ $task->assignee ? $task->assignee->short_name : 'Не назначен' }}</td>
            <td>{{ $task->due_date ? date('d.m.Y', strtotime($task->due_date)) : '—' }}</td>
            <td>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $task->progress }}%"></div>
                </div>
                {{ $task->progress }}%
            </td>
            <td>{{ $task->created_at ? date('d.m.Y', strtotime($task->created_at)) : '—' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="11" style="text-align: center; padding: 40px;">
                Нет задач для отображения
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="footer">
    <p>Отчет сгенерирован автоматически в системе GofraLub</p>
    <p>© {{ date('Y') }} GofraLub - Все права защищены</p>
</div>
</body>
</html>
