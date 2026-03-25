<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoComment;
use App\Models\TodoSubtask;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->get('type', 'all');
        $status = $request->get('status', 'all');
        $priority = $request->get('priority', 'all');

        $query = Todo::with(['creator', 'assignee', 'department', 'participants', 'subtasks'])
            ->byUser($user->id);

        if ($type === 'personal') {
            $query->personal();
        } elseif ($type === 'shared') {
            $query->shared();
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }

        $todos = $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->orderBy('due_date')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => Todo::byUser($user->id)->count(),
            'pending' => Todo::byUser($user->id)->where('status', Todo::STATUS_PENDING)->count(),
            'in_progress' => Todo::byUser($user->id)->where('status', Todo::STATUS_IN_PROGRESS)->count(),
            'completed' => Todo::byUser($user->id)->where('status', Todo::STATUS_COMPLETED)->count(),
            'overdue' => Todo::byUser($user->id)
                ->where('due_date', '<', now())
                ->whereNotIn('status', [Todo::STATUS_COMPLETED, Todo::STATUS_CANCELLED])
                ->count(),
            'personal_count' => Todo::byUser($user->id)->where('visibility', 'personal')->count(),
            'department_count' => Todo::byUser($user->id)->where('visibility', 'department')->count(),
            'company_count' => Todo::byUser($user->id)->where('visibility', 'company')->count(),
            'assigned_count' => Todo::byUser($user->id)->where('assigned_to', $user->id)->count(),
            'current_user_id' => $user->id,
        ];

        // Получаем всех активных пользователей для выбора
        $users = User::with(['department', 'position'])
            ->whereNotNull('approved_at')
            ->whereNotNull('email_verified_at')
            ->orderBy('last_name')
            ->get(['id', 'last_name', 'first_name', 'patronymic', 'nickname', 'department_id']);

        $departments = Department::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Todo/Index', [
            'todos' => $todos,
            'stats' => $stats,
            'users' => $users,
            'departments' => $departments,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'priority' => $priority,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:task,urgent,reminder,idea',
            'priority' => 'required|in:low,medium,high,urgent',
            'visibility' => 'required|in:personal,department,company',
            'due_date' => 'nullable|string',
            'due_time' => 'nullable|string',
            'reminder_at' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = Todo::STATUS_PENDING;

        // Обработка due_date и due_time
        if (!empty($validated['due_date']) && !empty($validated['due_time'])) {
            $validated['due_date'] = date('Y-m-d H:i:s', strtotime($validated['due_date'] . ' ' . $validated['due_time']));
        } elseif (!empty($validated['due_date'])) {
            $validated['due_date'] = date('Y-m-d', strtotime($validated['due_date']));
        } else {
            $validated['due_date'] = null;
        }

        // Обработка reminder_at
        if (!empty($validated['reminder_at'])) {
            $validated['reminder_at'] = date('Y-m-d H:i:s', strtotime($validated['reminder_at']));
        } else {
            $validated['reminder_at'] = null;
        }

        // Убираем временное поле due_time
        unset($validated['due_time']);

        // Для личных задач назначаем на себя
        if ($validated['visibility'] === 'personal') {
            $validated['assigned_to'] = $request->user()->id;
            $validated['department_id'] = null;
        }

        // Если assigned_to пустой, устанавливаем null
        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = null;
        }

        // Извлекаем participants для отдельной обработки
        $participants = $validated['participants'] ?? [];
        unset($validated['participants']);

        DB::beginTransaction();
        try {
            $todo = Todo::create($validated);

            // Добавляем участников (теперь это массив ID)
            if (!empty($participants) && is_array($participants)) {
                $todo->participants()->attach($participants);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Задача успешно создана');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Todo creation error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Ошибка при создании задачи: ' . $e->getMessage()]);
        }
    }

    public function show(Todo $todo)
    {
        $this->authorizeAccess($todo);

        $todo->load(['creator', 'assignee', 'department', 'participants', 'comments.user', 'subtasks']);

        $users = User::with(['department', 'position'])
            ->whereNotNull('approved_at')
            ->orderBy('last_name')
            ->get(['id', 'last_name', 'first_name', 'patronymic', 'nickname', 'department_id']);

        return Inertia::render('Todo/Show', [
            'todo' => $todo,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Todo $todo)
    {
        $this->authorizeAccess($todo);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'status' => 'sometimes|required|in:backlog,pending,in_progress,review,completed,cancelled',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($todo->visibility === 'personal') {
            $validated['assigned_to'] = $todo->created_by;
        }

        $todo->update($validated);

        return redirect()->back()->with('success', 'Задача обновлена');
    }

    public function destroy(Todo $todo)
    {
        $this->authorizeDelete($todo);

        $todo->delete();

        return redirect()->route('todos.index')->with('success', 'Задача удалена');
    }

    public function addComment(Request $request, Todo $todo)
    {
        $this->authorizeAccess($todo);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        TodoComment::create([
            'content' => $validated['content'],
            'todo_id' => $todo->id,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', 'Комментарий добавлен');
    }

    public function addSubtask(Request $request, Todo $todo)
    {
        $this->authorizeAccess($todo);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        TodoSubtask::create([
            'title' => $validated['title'],
            'todo_id' => $todo->id,
        ]);

        return redirect()->back()->with('success', 'Подзадача добавлена');
    }

    public function toggleSubtask(TodoSubtask $subtask)
    {
        $this->authorizeAccess($subtask->todo);

        $subtask->update(['is_completed' => !$subtask->is_completed]);

        return redirect()->back()->with('success', 'Статус подзадачи изменен');
    }

    public function addParticipant(Request $request, Todo $todo)
    {
        $this->authorizeAccess($todo);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if ($todo->visibility !== 'personal') {
            $todo->participants()->syncWithoutDetaching([$validated['user_id']]);
        }

        return redirect()->back()->with('success', 'Участник добавлен');
    }

    public function removeParticipant(Todo $todo, User $user)
    {
        $this->authorizeAccess($todo);

        if ($todo->visibility !== 'personal') {
            $todo->participants()->detach($user->id);
        }

        return redirect()->back()->with('success', 'Участник удален');
    }

    private function authorizeAccess(Todo $todo)
    {
        $user = auth()->user();

        $canAccess = $todo->created_by === $user->id ||
            $todo->assigned_to === $user->id ||
            $todo->participants()->where('user_id', $user->id)->exists() ||
            $user->hasRole('admin') ||
            ($user->hasRole('manager') && $todo->department_id === $user->department_id);

        if (!$canAccess) {
            abort(403, 'У вас нет доступа к этой задаче');
        }
    }

    private function authorizeDelete(Todo $todo)
    {
        $user = auth()->user();

        $canDelete = $todo->created_by === $user->id ||
            $user->hasRole('admin') ||
            ($user->hasRole('manager') && $todo->department_id === $user->department_id);

        if (!$canDelete) {
            abort(403, 'У вас нет прав на удаление этой задачи');
        }
    }
}
