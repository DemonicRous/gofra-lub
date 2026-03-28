<?php
// app/Services/TaskService.php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskHistory;
use App\Models\TaskComment;
use App\Models\TaskSubtask;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    protected $cacheTTL = 3600; // 1 час

    /**
     * Создание задачи с оптимизацией
     */
    public function createTask(array $data, User $creator): Task
    {
        return DB::transaction(function () use ($data, $creator) {
            // Генерация UUID
            $data['uuid'] = (string) Str::uuid();
            $data['created_by'] = $creator->id;

            // Обработка дат
            if (!empty($data['due_date'])) {
                $data['due_date'] = $this->parseDateTime($data['due_date'], $data['due_time'] ?? null);
            }

            if (!empty($data['reminder_at'])) {
                $data['reminder_at'] = $this->parseDateTime($data['reminder_at'], $data['reminder_time'] ?? null);
            }

            // Для личных задач назначаем на создателя
            if ($data['visibility'] === Task::VISIBILITY_PERSONAL) {
                $data['assigned_to'] = $creator->id;
            }

            // Создание задачи
            $task = Task::create($data);

            // Добавление участников
            if (!empty($data['participants'])) {
                $task->participants()->attach($data['participants'], ['role' => 'participant']);
            }

            // Добавление тегов
            if (!empty($data['tags'])) {
                $task->tags()->attach($data['tags']);
            }

            // Логирование создания
            $this->logHistory($task, $creator, 'created', null, $task->toArray());

            // Очистка кэша
            $this->clearCache($creator);

            return $task;
        });
    }

    /**
     * Обновление задачи
     */
    public function updateTask(Task $task, array $data, User $updater): Task
    {
        return DB::transaction(function () use ($task, $data, $updater) {
            $oldData = $task->toArray();

            $task->update($data);

            // Если статус изменился на completed, устанавливаем дату завершения
            if (isset($data['status']) && $data['status'] === Task::STATUS_COMPLETED && !$task->completed_at) {
                $task->completed_at = now();
                $task->save();
            }

            // Если статус изменился на in_progress, устанавливаем дату начала
            if (isset($data['status']) && $data['status'] === Task::STATUS_IN_PROGRESS && !$task->started_at) {
                $task->started_at = now();
                $task->save();
            }

            // Логирование изменений
            $changes = array_diff_assoc($task->toArray(), $oldData);
            foreach ($changes as $field => $newValue) {
                $this->logHistory($task, $updater, $field, $oldData[$field] ?? null, $newValue);
            }

            // Очистка кэша
            $this->clearCache($updater);

            return $task->fresh();
        });
    }

    /**
     * Быстрый поиск задач (с кэшированием)
     */
    public function getTasksForUser(User $user, array $filters = [], int $perPage = 15)
    {
        $cacheKey = "user_tasks_{$user->id}_" . md5(json_encode($filters) . $perPage);

        $this->rememberTaskKey($user, $cacheKey);

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($user, $filters, $perPage) {
            $query = Task::with([
                'creator' => function ($q) {
                    $q->select('id', 'last_name', 'first_name', 'patronymic', 'nickname', 'position_id');
                },
                'assignee' => function ($q) {
                    $q->select('id', 'last_name', 'first_name', 'patronymic', 'nickname', 'position_id');
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
            $query->visibleTo($user);

            // Применяем фильтры (только если не 'all')
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
                $query->where(function ($q) use ($filters) {
                    $q->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('description', 'like', "%{$filters['search']}%");
                });
            }

            // Сортировка
            $sortField = $filters['sort'] ?? 'priority';
            $sortOrder = $filters['order'] ?? 'desc';

            if ($sortField === 'priority') {
                $query->orderByRaw("FIELD(priority, 'critical', 'urgent', 'high', 'medium', 'low')");
            } elseif ($sortField === 'due_date') {
                $query->orderBy('due_date', $sortOrder);
            } elseif ($sortField === 'created_at') {
                $query->orderBy('created_at', $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $tasks = $query->paginate($perPage);

            // Преобразуем данные для корректного отображения имен
            $tasks->getCollection()->transform(function ($task) {
                // Добавляем вычисляемые поля для удобства
                $task->creator_name = $task->creator ? $task->creator->full_name : '—';
                $task->creator_short_name = $task->creator ? $task->creator->short_name : '—';
                $task->assignee_name = $task->assignee ? $task->assignee->full_name : 'Не назначен';
                $task->assignee_short_name = $task->assignee ? $task->assignee->short_name : 'Не назначен';
                return $task;
            });

            return $tasks;
        });
    }

    /**
     * Получение статистики задач
     */
    public function getTaskStats(User $user): array
    {
        $cacheKey = "user_tasks_stats_{$user->id}";

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($user) {
            $tasks = Task::visibleTo($user)->select('status', 'priority', 'due_date', 'progress')->get();

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
                'by_priority' => [
                    'low' => $tasks->where('priority', Task::PRIORITY_LOW)->count(),
                    'medium' => $tasks->where('priority', Task::PRIORITY_MEDIUM)->count(),
                    'high' => $tasks->where('priority', Task::PRIORITY_HIGH)->count(),
                    'urgent' => $tasks->where('priority', Task::PRIORITY_URGENT)->count(),
                    'critical' => $tasks->where('priority', Task::PRIORITY_CRITICAL)->count(),
                ],
                'overdue' => $tasks->filter(function ($task) {
                    return $task->isOverdue();
                })->count(),
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                'average_progress' => $total > 0 ? round($tasks->avg('progress'), 2) : 0,
            ];
        });
    }

    /**
     * Добавление комментария
     */
    public function addComment(Task $task, User $user, string $content): TaskComment
    {
        return DB::transaction(function () use ($task, $user, $content) {
            $comment = $task->comments()->create([
                'content' => $content,
                'user_id' => $user->id,
            ]);

            // Очистка кэша
            $this->clearCache($user);

            return $comment;
        });
    }

    /**
     * Добавление подзадачи
     */
    public function addSubtask(Task $task, array $data, User $user): TaskSubtask
    {
        return DB::transaction(function () use ($task, $data, $user) {
            $maxOrder = $task->subtasks()->max('order') ?? 0;

            $subtask = $task->subtasks()->create([
                'title' => $data['title'],
                'is_completed' => false,
                'order' => $maxOrder + 1,
            ]);

            $this->logHistory($task, $user, 'subtask_added', null, $subtask->title);
            $this->clearCache($user);

            return $subtask;
        });
    }

    /**
     * Обновление статуса подзадачи
     */
    public function toggleSubtask(TaskSubtask $subtask, User $user): TaskSubtask
    {
        return DB::transaction(function () use ($subtask, $user) {
            $oldStatus = $subtask->is_completed;
            $subtask->is_completed = !$oldStatus;
            $subtask->save();

            // Обновляем прогресс родительской задачи
            $task = $subtask->task;
            $totalSubtasks = $task->subtasks()->count();
            $completedSubtasks = $task->subtasks()->where('is_completed', true)->count();

            $progress = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
            $task->progress = $progress;

            if ($progress === 100 && $task->status !== Task::STATUS_COMPLETED) {
                $task->status = Task::STATUS_IN_REVIEW;
                $task->save();
            } elseif ($progress < 100 && $task->status === Task::STATUS_IN_REVIEW) {
                $task->status = Task::STATUS_IN_PROGRESS;
                $task->save();
            } else {
                $task->save();
            }

            $this->logHistory($task, $user, 'subtask_toggled', $oldStatus, $subtask->is_completed);
            $this->clearCache($user);

            return $subtask;
        });
    }

    /**
     * Массовое обновление статусов
     */
    public function bulkUpdateStatus(array $taskIds, string $status, User $user): int
    {
        $count = 0;

        DB::transaction(function () use ($taskIds, $status, $user, &$count) {
            $tasks = Task::whereIn('id', $taskIds)
                ->visibleTo($user)
                ->get();

            foreach ($tasks as $task) {
                $oldStatus = $task->status;
                $task->status = $status;
                $task->save();

                if ($status === Task::STATUS_COMPLETED && !$task->completed_at) {
                    $task->completed_at = now();
                    $task->save();
                }

                if ($status === Task::STATUS_IN_PROGRESS && !$task->started_at) {
                    $task->started_at = now();
                    $task->save();
                }

                $this->logHistory($task, $user, 'bulk_status_update', $oldStatus, $status);
                $count++;
            }
        });

        $this->clearCache($user);

        return $count;
    }

    /**
     * Удаление задачи (мягкое удаление)
     */
    public function deleteTask(Task $task, User $user): bool
    {
        return DB::transaction(function () use ($task, $user) {
            $this->logHistory($task, $user, 'deleted', null, $task->toArray());
            $result = $task->delete();
            $this->clearCache($user);
            return $result;
        });
    }

    /**
     * Восстановление задачи
     */
    public function restoreTask(Task $task, User $user): bool
    {
        return DB::transaction(function () use ($task, $user) {
            $result = $task->restore();
            $this->logHistory($task, $user, 'restored', null, $task->toArray());
            $this->clearCache($user);
            return $result;
        });
    }

    /**
     * Получение задач, требующих напоминания
     */
    public function getTasksForReminder(): \Illuminate\Support\Collection
    {
        return Task::where('reminder_at', '<=', now())
            ->where('reminder_sent', false)
            ->whereNotIn('status', [Task::STATUS_COMPLETED, Task::STATUS_CANCELLED])
            ->get();
    }

    /**
     * Отметка о отправке напоминания
     */
    public function markReminderSent(Task $task): void
    {
        $task->reminder_sent = true;
        $task->save();
    }

    /**
     * Парсинг даты и времени
     */
    protected function parseDateTime($date, $time = null): ?string
    {
        if (!$date) {
            return null;
        }

        if ($time) {
            return date('Y-m-d H:i:s', strtotime("{$date} {$time}"));
        }

        return date('Y-m-d', strtotime($date));
    }

    /**
     * Логирование истории
     */
    protected function logHistory(Task $task, User $user, string $field, $oldValue, $newValue): void
    {
        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'field' => $field,
            'old_value' => is_array($oldValue) ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : (string) $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : (string) $newValue,
        ]);
    }

    /**
     * Очистка кэша пользователя
     */
    public function clearCache(User $user): void
    {
        // Удаляем ключи кэша статистики
        Cache::forget("user_tasks_stats_{$user->id}");

        // Удаляем все возможные ключи кэша задач пользователя
        $keys = Cache::get("user_tasks_keys_{$user->id}", []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Сохраняем пустой список
        Cache::put("user_tasks_keys_{$user->id}", [], 3600);

        // Также удаляем кэш тегов (если используется Redis)
        try {
            Cache::tags(['tasks', "user_{$user->id}"])->flush();
        } catch (\Exception $e) {
            // Если теги не поддерживаются, игнорируем ошибку
        }
    }

    /**
     * Сохраняем ключ кэша для последующей очистки
     */
    protected function rememberTaskKey(User $user, string $key): void
    {
        $keys = Cache::get("user_tasks_keys_{$user->id}", []);
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            Cache::put("user_tasks_keys_{$user->id}", $keys, 3600);
        }
    }

    /**
     * Получение детальной информации о задаче с загрузкой всех связей
     */
    /**
     * Получение детальной информации о задаче с загрузкой всех связей
     */
    public function getTaskDetails(Task $task): Task
    {
        $task->load([
            'creator' => function ($q) {
                $q->select('id', 'last_name', 'first_name', 'patronymic', 'nickname', 'position_id');
            },
            'assignee' => function ($q) {
                $q->select('id', 'last_name', 'first_name', 'patronymic', 'nickname', 'position_id');
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

        // Добавляем вычисляемые поля
        $task->creator_name = $task->creator ? $task->creator->full_name : '—';
        $task->assignee_name = $task->assignee ? $task->assignee->full_name : 'Не назначен';

        foreach ($task->comments as $comment) {
            $comment->user_name = $comment->user ? $comment->user->full_name : '—';
        }

        return $task;
    }
}
