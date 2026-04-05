<?php
// app/Http/Controllers/AuditController.php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\AuditMedia;
use App\Models\AuditComment;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuditController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Начать аудит (сменить статус с черновика на в процессе)
     */
    public function start(Audit $audit, Request $request)
    {
        $this->authorizeEdit($audit, $request->user());

        $audit->markAsInProgress();

        return redirect()->back()->with('success', 'Аудит начат');
    }

    /**
     * Главная страница аудитов
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $filters = [
            'status' => $request->get('status', 'all'),
            'audit_type' => $request->get('audit_type', 'all'),
            'search' => $request->get('search', ''),
            'date_from' => $request->get('date_from', ''),
            'date_to' => $request->get('date_to', '')
        ];

        $audits = Audit::with([
            'creator:id,last_name,first_name,patronymic,nickname',
            'assignee:id,last_name,first_name,patronymic,nickname',
            'media' => function ($q) {
                $q->where('media_type', 'photo')->limit(3);
            },
            'comments'
        ])->visibleTo($user);

        // Применяем фильтры
        if ($filters['status'] !== 'all') {
            $audits->where('status', $filters['status']);
        }

        if ($filters['audit_type'] !== 'all') {
            $audits->where('audit_type', $filters['audit_type']);
        }

        if ($filters['date_from']) {
            $audits->whereDate('audit_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $audits->whereDate('audit_date', '<=', $filters['date_to']);
        }

        if ($filters['search']) {
            $audits->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('client_name', 'like', "%{$filters['search']}%")
                    ->orWhere('address', 'like', "%{$filters['search']}%");
            });
        }

        $audits = $audits->orderBy('audit_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Добавляем вычисляемые поля
        $audits->getCollection()->transform(function ($audit) {
            $audit->creator_name = $audit->creator ? $audit->creator->short_name : '—';
            $audit->assignee_name = $audit->assignee ? $audit->assignee->short_name : 'Не назначен';
            $audit->status_name = $this->getStatusName($audit->status);
            $audit->type_name = $this->getAuditTypeName($audit->audit_type);
            return $audit;
        });

        // Статистика
        $stats = $this->getAuditStats($user);

        // Список сотрудников для назначения
        $users = \App\Models\User::whereNotNull('approved_at')
            ->select('id', 'last_name', 'first_name', 'patronymic', 'nickname')
            ->orderBy('last_name')
            ->get()
            ->map(function ($user) {
                $user->full_name = $user->full_name;
                $user->short_name = $user->short_name;
                return $user;
            });

        return Inertia::render('Audits/Index', [
            'audits' => $audits,
            'stats' => $stats,
            'users' => $users,
            'filters' => $filters
        ]);
    }

    /**
     * Просмотр аудита
     */
    public function show(Audit $audit, Request $request)
    {
        $this->authorizeAccess($audit, $request->user());

        $audit->load([
            'creator:id,last_name,first_name,patronymic,nickname',
            'assignee:id,last_name,first_name,patronymic,nickname',
            'media',
            'comments' => function ($q) {
                $q->with(['user:id,last_name,first_name,patronymic,nickname'])
                    ->latest();
            },
            'relatedTask:id,title,status'
        ]);

        // Вычисляемые поля
        $audit->creator_name = $audit->creator ? $audit->creator->full_name : '—';
        $audit->creator_short = $audit->creator ? $audit->creator->short_name : '—';
        $audit->assignee_name = $audit->assignee ? $audit->assignee->full_name : 'Не назначен';
        $audit->assignee_short = $audit->assignee ? $audit->assignee->short_name : 'Не назначен';
        $audit->status_name = $this->getStatusName($audit->status);
        $audit->type_name = $this->getAuditTypeName($audit->audit_type);

        // Форматированное время
        $audit->start_time_formatted = $audit->start_time ? date('H:i', strtotime($audit->start_time)) : null;
        $audit->end_time_formatted = $audit->end_time ? date('H:i', strtotime($audit->end_time)) : null;

        // Права на редактирование
        $audit->can_be_edited = $this->canEditAudit($audit, $request->user());

        foreach ($audit->comments as $comment) {
            $comment->user_name = $comment->user ? $comment->user->full_name : '—';
            $comment->user_short = $comment->user ? $comment->user->short_name : '—';
        }

        return Inertia::render('Audits/Show', [
            'audit' => $audit
        ]);
    }

    /**
     * Проверка права на редактирование аудита (без выбрасывания исключения)
     */
    private function canEditAudit(Audit $audit, $user): bool
    {
        return $audit->created_by === $user->id ||
            $user->hasRole('admin') ||
            ($user->hasRole('manager') && $audit->department_id === $user->department_id);
    }

    /**
     * Создание аудита (форма)
     */
    public function create(Request $request)
    {
        $users = \App\Models\User::whereNotNull('approved_at')
            ->select('id', 'last_name', 'first_name', 'patronymic')
            ->orderBy('last_name')
            ->get();

        $tasks = $request->user()->tasks()
            ->whereIn('status', ['in_progress', 'todo'])
            ->select('id', 'title')
            ->get();

        return Inertia::render('Audits/Create', [
            'users' => $users,
            'tasks' => $tasks
        ]);
    }

    /**
     * Сохранение аудита
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_name' => 'nullable|string|max:255',
            'client_contact' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'object_name' => 'nullable|string|max:255',
            'audit_type' => 'required|in:measurement,production_line,quality_check,consultation,other',
            'audit_date' => 'nullable|date',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'related_task_id' => 'nullable|exists:tasks,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        $audit = $this->auditService->createAudit($validated, $request->user());

        return redirect()->route('audits.show', $audit)
            ->with('success', 'Аудит успешно создан');
    }

    /**
     * Обновление аудита
     */
    public function update(Request $request, Audit $audit)
    {
        $this->authorizeEdit($audit, $request->user());

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'findings' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'client_name' => 'nullable|string|max:255',
            'client_contact' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'object_name' => 'nullable|string|max:255',
            'audit_type' => 'sometimes|in:measurement,production_line,quality_check,consultation,other',
            'status' => 'sometimes|in:draft,in_progress,completed,cancelled',
            'audit_date' => 'nullable|date',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $audit = $this->auditService->updateAudit($audit, $validated, $request->user());

        return redirect()->back()->with('success', 'Аудит обновлен');
    }

    /**
     * Завершение аудита
     */
    public function complete(Request $request, Audit $audit)
    {
        $this->authorizeEdit($audit, $request->user());

        $validated = $request->validate([
            'findings' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'signature_data' => 'nullable|string' // Подпись в base64
        ]);

        $audit = $this->auditService->completeAudit($audit, $validated, $request->user());

        return redirect()->route('audits.show', $audit)
            ->with('success', 'Аудит завершен и подписан');
    }

    /**
     * Удаление аудита
     */
    public function destroy(Audit $audit, Request $request)
    {
        $this->authorizeDelete($audit, $request->user());

        $audit->delete();

        return redirect()->route('audits.index')
            ->with('success', 'Аудит удален');
    }

    /**
     * Загрузка медиафайлов
     */
    public function uploadMedia(Request $request, Audit $audit)
    {
        $this->authorizeEdit($audit, $request->user());

        $request->validate([
            'file' => 'required|file|max:20480', // Максимум 20MB
            'media_type' => 'required|in:photo,video,document,drawing,other',
            'description' => 'nullable|string|max:500'
        ]);

        $file = $request->file('file');
        $path = $file->store('audits/' . $audit->id, 'public');

        $media = $audit->media()->create([
            'uploaded_by' => $request->user()->id,
            'filename' => $file->hashName(),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'disk' => 'public',
            'path' => $path,
            'size' => $file->getSize(),
            'media_type' => $request->media_type,
            'description' => $request->description,
            'sort_order' => $audit->media()->count()
        ]);

        return response()->json([
            'success' => true,
            'media' => $media,
            'url' => $media->url
        ]);
    }

    /**
     * Удаление медиафайла
     */
    public function deleteMedia(AuditMedia $media, Request $request)
    {
        $this->authorizeEdit($media->audit, $request->user());

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Добавление комментария с вложениями
     */
    public function addComment(Request $request, Audit $audit)
    {
        $this->authorizeAccess($audit, $request->user());

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'exists:audit_media,id'
        ]);

        $comment = $this->auditService->addComment(
            $audit,
            $request->user(),
            $validated['content'],
            $validated['attachments'] ?? []
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'comment' => $comment->load('media')]);
        }

        return redirect()->back()->with('success', 'Комментарий добавлен');
    }

    /**
     * Загрузка медиа для комментария
     */
    public function uploadCommentMedia(Request $request, Audit $audit)
    {
        $this->authorizeAccess($audit, $request->user());

        $request->validate([
            'file' => 'required|file|max:20480', // 20MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('audits/' . $audit->id . '/comments', 'public');

        $media = $audit->media()->create([
            'uploaded_by' => $request->user()->id,
            'filename' => $file->hashName(),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'disk' => 'public',
            'path' => $path,
            'size' => $file->getSize(),
            'media_type' => str_starts_with($file->getMimeType(), 'image/') ? 'photo' : 'document',
            'is_public' => true
        ]);

        return response()->json([
            'success' => true,
            'media' => $media,
            'url' => $media->url
        ]);
    }

    /**
     * Экспорт аудита в PDF
     */
    public function exportPdf(Audit $audit, Request $request)
    {
        $this->authorizeAccess($audit, $request->user());

        try {
            // Увеличиваем лимиты для обработки больших изображений
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', 300);

            $pdf = $this->auditService->exportToPdf($audit);

            // Формируем имя файла
            $filename = "audit_{$audit->id}_" . date('Y-m-d_His') . ".pdf";

            // Возвращаем PDF для скачивания
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('PDF export error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при формировании PDF: ' . $e->getMessage());
        }
    }

    /**
     * Статистика аудитов
     */
    private function getAuditStats($user): array
    {
        $audits = Audit::visibleTo($user)->get();

        return [
            'total' => $audits->count(),
            'by_status' => [
                'draft' => $audits->where('status', Audit::STATUS_DRAFT)->count(),
                'in_progress' => $audits->where('status', Audit::STATUS_IN_PROGRESS)->count(),
                'completed' => $audits->where('status', Audit::STATUS_COMPLETED)->count(),
                'cancelled' => $audits->where('status', Audit::STATUS_CANCELLED)->count()
            ],
            'by_type' => [
                'measurement' => $audits->where('audit_type', Audit::TYPE_MEASUREMENT)->count(),
                'production_line' => $audits->where('audit_type', Audit::TYPE_PRODUCTION_LINE)->count(),
                'quality_check' => $audits->where('audit_type', Audit::TYPE_QUALITY_CHECK)->count(),
                'consultation' => $audits->where('audit_type', Audit::TYPE_CONSULTATION)->count()
            ],
            'this_month' => $audits->where('audit_date', '>=', now()->startOfMonth())->count()
        ];
    }

    private function getStatusName($status): string
    {
        $map = [
            'draft' => 'Черновик',
            'in_progress' => 'В процессе',
            'completed' => 'Завершен',
            'cancelled' => 'Отменен'
        ];
        return $map[$status] ?? $status;
    }

    private function getAuditTypeName($type): string
    {
        $map = [
            'measurement' => 'Замеры',
            'production_line' => 'Производственная линия',
            'quality_check' => 'Проверка качества',
            'consultation' => 'Консультация',
            'other' => 'Другое'
        ];
        return $map[$type] ?? $type;
    }

    private function authorizeAccess(Audit $audit, $user): void
    {
        $canAccess = $audit->created_by === $user->id ||
            $audit->assigned_to === $user->id ||
            $user->hasRole('admin') ||
            ($user->hasRole('manager') && $audit->department_id === $user->department_id);

        if (!$canAccess) {
            abort(403, 'У вас нет доступа к этому аудиту');
        }
    }

    private function authorizeEdit(Audit $audit, $user): void
    {
        $canEdit = $audit->created_by === $user->id ||
            $user->hasRole('admin') ||
            ($user->hasRole('manager') && $audit->department_id === $user->department_id);

        if (!$canEdit) {
            abort(403, 'У вас нет прав на редактирование этого аудита');
        }
    }

    private function authorizeDelete(Audit $audit, $user): void
    {
        $canDelete = $audit->created_by === $user->id || $user->hasRole('admin');

        if (!$canDelete) {
            abort(403, 'У вас нет прав на удаление этого аудита');
        }
    }
}
