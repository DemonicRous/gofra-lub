<?php
// app/Http/Controllers/TaskController.php

namespace App\Http\Controllers;

use App\Exports\TasksExport;
use App\Exports\TasksPdfExport;
use App\Models\Task;
use App\Models\User;
use App\Models\Tag;
use App\Models\Project;
use App\Models\TaskSubtask;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Главная страница задач
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Получаем фильтры из запроса с правильными значениями по умолчанию
        $filters = [
            'status' => $request->get('status', 'all'),
            'priority' => $request->get('priority', 'all'),
            'type' => $request->get('type', 'all'),
            'visibility' => $request->get('visibility', 'all'),
            'search' => $request->get('search', ''),
            'sort' => $request->get('sort', 'priority'),
            'order' => $request->get('order', 'desc'),
        ];

        // Получаем задачи с фильтрацией - ВАЖНО: загружаем creator как объект
        $tasks = Task::with([
            'creator' => function ($q) {
                $q->select('id', 'last_name', 'first_name', 'patronymic', 'nickname');
            },
            'assignee' => function ($q) {
                $q->select('id', 'last_name', 'first_name', 'patronymic', 'nickname');
            },
            'department:id,name',
            'project:id,name,color',
            'tags:id,name,color',
            'subtasks' => function ($q) {
                $q->select('id', 'title', 'is_completed', 'task_id')->orderBy('order');
            },
            'participants' => function ($q) {
                $q->select('users.id', 'users.last_name', 'users.first_name', 'users.patronymic', 'users.nickname');
            }
        ]);

        // Применяем видимость
        $tasks->visibleTo($user);

        // Применяем фильтры
        if ($filters['status'] !== 'all') {
            $tasks->where('status', $filters['status']);
        }

        if ($filters['priority'] !== 'all') {
            $tasks->where('priority', $filters['priority']);
        }

        if ($filters['type'] !== 'all') {
            $tasks->where('type', $filters['type']);
        }

        if ($filters['visibility'] !== 'all') {
            $tasks->where('visibility', $filters['visibility']);
        }

        if (!empty($filters['search'])) {
            $tasks->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        // Сортировка
        if ($filters['sort'] === 'priority') {
            $tasks->orderByRaw("FIELD(priority, 'critical', 'urgent', 'high', 'medium', 'low')");
        } elseif ($filters['sort'] === 'due_date') {
            $tasks->orderBy('due_date', $filters['order']);
        } else {
            $tasks->orderBy('created_at', $filters['order']);
        }

        $tasks = $tasks->paginate($request->get('per_page', 15));

        // Добавляем вычисляемые поля для имён (short_name)
        $tasks->getCollection()->transform(function ($task) {
            $task->creator_name = $task->creator ? $task->creator->full_name : '—';
            $task->creator_short = $task->creator ? $task->creator->short_name : '—';
            $task->assignee_name = $task->assignee ? $task->assignee->full_name : 'Не назначен';
            $task->assignee_short = $task->assignee ? $task->assignee->short_name : 'Не назначен';
            $task->is_overdue = $task->isOverdue();
            return $task;
        });

        // Получаем статистику
        $stats = $this->getTaskStats($user);

        // Получаем теги и проекты
        $tags = Tag::all(['id', 'name', 'color']);
        $projects = $user->projects()->withCount('tasks')->get(['id', 'name', 'color']);
        $users = User::whereNotNull('approved_at')
            ->select('id', 'last_name', 'first_name', 'patronymic', 'nickname')
            ->orderBy('last_name')
            ->get()
            ->map(function ($user) {
                $user->full_name = $user->full_name;
                $user->short_name = $user->short_name;
                return $user;
            });

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'stats' => $stats,
            'tags' => $tags,
            'projects' => $projects,
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    /**
     * Создание задачи
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:task,urgent,reminder,idea,bug,feature',
            'priority' => 'required|in:low,medium,high,urgent,critical',
            'visibility' => 'required|in:personal,department,company,project',
            'project_id' => 'nullable|exists:projects,id',
            'due_date' => 'nullable|string',
            'due_time' => 'nullable|string',
            'reminder_at' => 'nullable|string',
            'reminder_time' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $task = $this->taskService->createTask($validated, $request->user());

        return redirect()->back()->with('success', 'Задача успешно создана');
    }

    /**
     * Просмотр задачи
     */
    public function show(Task $task, Request $request)
    {
        $this->authorizeAccess($task, $request->user());

        $task->load([
            'creator' => function ($q) {
                $q->select('id', 'last_name', 'first_name', 'patronymic', 'nickname');
            },
            'assignee' => function ($q) {
                $q->select('id', 'last_name', 'first_name', 'patronymic', 'nickname');
            },
            'department:id,name',
            'project:id,name,color,description',
            'tags:id,name,color',
            'subtasks:id,title,is_completed,task_id,order',
            'comments' => function ($query) {
                $query->with(['user' => function ($q) {
                    $q->select('id', 'last_name', 'first_name', 'patronymic', 'nickname');
                }])->latest();
            },
            'participants' => function ($q) {
                $q->select('users.id', 'users.last_name', 'users.first_name', 'users.patronymic', 'users.nickname');
            },
            'history' => function ($query) {
                $query->with(['user' => function ($q) {
                    $q->select('id', 'last_name', 'first_name', 'patronymic');
                }])->latest()->limit(20);
            },
        ]);

        // Добавляем вычисляемые поля для имён (short_name)
        $task->creator_name = $task->creator ? $task->creator->full_name : '—';
        $task->creator_short = $task->creator ? $task->creator->short_name : '—';  // Добавлено
        $task->assignee_name = $task->assignee ? $task->assignee->full_name : 'Не назначен';
        $task->assignee_short = $task->assignee ? $task->assignee->short_name : 'Не назначен';  // Добавлено
        $task->is_overdue = $task->isOverdue();

        // Для комментариев добавляем имена
        foreach ($task->comments as $comment) {
            $comment->user_name = $comment->user ? $comment->user->full_name : '—';
            $comment->user_short = $comment->user ? $comment->user->short_name : '—';
        }

        return Inertia::render('Tasks/Show', [
            'task' => $task,
        ]);
    }

    /**
     * Обновление задачи
     */
    public function update(Request $request, Task $task)
    {
        $this->authorizeAccess($task, $request->user());

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent,critical',
            'status' => 'sometimes|in:backlog,todo,in_progress,in_review,completed,cancelled,archived',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task = $this->taskService->updateTask($task, $validated, $request->user());

        return redirect()->back()->with('success', 'Задача обновлена');
    }

    /**
     * Удаление задачи
     */
    public function destroy(Task $task, Request $request)
    {
        $this->authorizeDelete($task, $request->user());

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Задача удалена');
    }

    /**
     * Добавление комментария
     */
    public function addComment(Request $request, Task $task)
    {
        $this->authorizeAccess($task, $request->user());

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment = $this->taskService->addComment($task, $request->user(), $validated['content']);

        return redirect()->back()->with('success', 'Комментарий добавлен');
    }

    /**
     * Добавление подзадачи
     */
    public function addSubtask(Request $request, Task $task)
    {
        $this->authorizeAccess($task, $request->user());

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $subtask = $this->taskService->addSubtask($task, $validated, $request->user());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'subtask' => $subtask]);
        }

        return redirect()->back()->with('success', 'Подзадача добавлена');
    }

    /**
     * Переключение статуса подзадачи
     */
    public function toggleSubtask(TaskSubtask $subtask, Request $request)
    {
        $this->authorizeAccess($subtask->task, $request->user());

        $this->taskService->toggleSubtask($subtask, $request->user());

        return redirect()->back()->with('success', 'Статус подзадачи изменен');
    }

    /**
     * Массовое обновление статусов
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'status' => 'required|in:todo,in_progress,completed,cancelled',
        ]);

        $count = $this->taskService->bulkUpdateStatus(
            $validated['task_ids'],
            $validated['status'],
            $request->user()
        );

        return redirect()->back()->with('success', "Обновлено задач: {$count}");
    }

    /**
     * Экспорт задач
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['status', 'priority', 'type']);

        $tasks = Task::visibleTo($user)
            ->when($filters, function ($query) use ($filters) {
                foreach ($filters as $key => $value) {
                    if ($value && $value !== 'all') {
                        $query->where($key, $value);
                    }
                }
            })
            ->get(['title', 'status', 'priority', 'due_date', 'created_at']);

        return response()->streamDownload(function () use ($tasks) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Название', 'Статус', 'Приоритет', 'Срок', 'Создана']);

            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $task->title,
                    $task->status,
                    $task->priority,
                    $task->due_date,
                    $task->created_at,
                ]);
            }

            fclose($handle);
        }, 'tasks_export_' . date('Y-m-d') . '.csv');
    }

    /**
     * Получение статистики задач
     */
    private function getTaskStats(User $user): array
    {
        $tasks = Task::visibleTo($user)->get();

        $total = $tasks->count();
        $completed = $tasks->where('status', Task::STATUS_COMPLETED)->count();

        return [
            'total' => $total,
            'by_status' => [
                'backlog' => $tasks->where('status', Task::STATUS_BACKLOG)->count(),
                'todo' => $tasks->where('status', Task::STATUS_TODO)->count(),
                'in_progress' => $tasks->where('status', Task::STATUS_IN_PROGRESS)->count(),
                'in_review' => $tasks->where('status', Task::STATUS_IN_REVIEW)->count(),
                'completed' => $completed,
                'cancelled' => $tasks->where('status', Task::STATUS_CANCELLED)->count(),
            ],
            'overdue' => $tasks->filter(function ($task) {
                return $task->isOverdue();
            })->count(),
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Проверка доступа к задаче
     */
    protected function authorizeAccess(Task $task, User $user): void
    {
        $canAccess = $task->created_by === $user->id ||
            $task->assigned_to === $user->id ||
            $task->participants()->where('user_id', $user->id)->exists() ||
            $user->hasRole('admin') ||
            ($user->hasRole('manager') && $task->department_id === $user->department_id) ||
            ($task->visibility === Task::VISIBILITY_DEPARTMENT && $task->department_id === $user->department_id) ||
            $task->visibility === Task::VISIBILITY_COMPANY;

        if (!$canAccess) {
            abort(403, 'У вас нет доступа к этой задаче');
        }
    }

    /**
     * Проверка прав на удаление
     */
    protected function authorizeDelete(Task $task, User $user): void
    {
        $canDelete = $task->created_by === $user->id ||
            $user->hasRole('admin') ||
            ($user->hasRole('manager') && $task->department_id === $user->department_id);

        if (!$canDelete) {
            abort(403, 'У вас нет прав на удаление этой задачи');
        }
    }

    /**
     * Экспорт задач в Excel
     */
    public function exportExcel(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['status', 'priority', 'type', 'visibility', 'search']);

        $tasks = Task::with(['creator', 'assignee'])
            ->visibleTo($user)
            ->when($filters, function ($query) use ($filters) {
                if (!empty($filters['status']) && $filters['status'] !== 'all') {
                    $query->where('status', $filters['status']);
                }
                if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
                    $query->where('priority', $filters['priority']);
                }
                if (!empty($filters['type']) && $filters['type'] !== 'all') {
                    $query->where('type', $filters['type']);
                }
                if (!empty($filters['visibility']) && $filters['visibility'] !== 'all') {
                    $query->where('visibility', $filters['visibility']);
                }
                if (!empty($filters['search'])) {
                    $query->where('title', 'like', "%{$filters['search']}%");
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $export = new TasksExport($tasks, $filters);

        return Excel::download($export, 'tasks_export_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Экспорт задач в PDF
     */
    public function exportPdf(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['status', 'priority', 'type', 'visibility', 'search']);

        $tasks = Task::with(['creator', 'assignee'])
            ->visibleTo($user)
            ->when($filters, function ($query) use ($filters) {
                if (!empty($filters['status']) && $filters['status'] !== 'all') {
                    $query->where('status', $filters['status']);
                }
                if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
                    $query->where('priority', $filters['priority']);
                }
                if (!empty($filters['type']) && $filters['type'] !== 'all') {
                    $query->where('type', $filters['type']);
                }
                if (!empty($filters['visibility']) && $filters['visibility'] !== 'all') {
                    $query->where('visibility', $filters['visibility']);
                }
                if (!empty($filters['search'])) {
                    $query->where('title', 'like', "%{$filters['search']}%");
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Добавляем вычисляемые поля
        foreach ($tasks as $task) {
            $task->status_name = $this->getStatusName($task->status);
            $task->priority_name = $this->getPriorityName($task->priority);
            $task->type_name = $this->getTypeName($task->type);
            $task->visibility_name = $this->getVisibilityName($task->visibility);
        }

        // Получаем статистику
        $stats = $this->getTaskStats($user);

        $export = new TasksPdfExport($tasks, $filters, $stats);

        return $export->download();
    }

// Вспомогательные методы для получения названий
    private function getStatusName($status): string
    {
        $map = [
            'backlog' => 'Бэклог',
            'todo' => 'К выполнению',
            'in_progress' => 'В работе',
            'in_review' => 'На проверке',
            'completed' => 'Выполнена',
            'cancelled' => 'Отменена',
        ];
        return $map[$status] ?? $status;
    }

    private function getPriorityName($priority): string
    {
        $map = [
            'low' => 'Низкий',
            'medium' => 'Средний',
            'high' => 'Высокий',
            'urgent' => 'Срочный',
            'critical' => 'Критический',
        ];
        return $map[$priority] ?? $priority;
    }

    private function getTypeName($type): string
    {
        $map = [
            'task' => 'Задача',
            'urgent' => 'Срочная',
            'reminder' => 'Напоминание',
            'idea' => 'Идея',
            'bug' => 'Ошибка',
            'feature' => 'Новая функция',
        ];
        return $map[$type] ?? $type;
    }

    private function getVisibilityName($visibility): string
    {
        $map = [
            'personal' => 'Личная',
            'department' => 'Отдел',
            'company' => 'Компания',
            'project' => 'Проект',
        ];
        return $map[$visibility] ?? $visibility;
    }

}
