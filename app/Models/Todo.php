<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'priority',
        'reminder_at',
        'reminder_sent',
        'urgent_notified_at',
        'due_date',
        'completed_at',
        'started_at',
        'recurring_type',
        'recurring_until',
        'parent_todo_id',
        'project_id',
        'progress',
        'visibility',
        'created_by',
        'assigned_to',
        'department_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reminder_at' => 'datetime',
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'reminder_sent' => 'boolean',
        'progress' => 'integer',
    ];

    // Типы задач
    const TYPE_TASK = 'task';
    const TYPE_URGENT = 'urgent';
    const TYPE_REMINDER = 'reminder';
    const TYPE_IDEA = 'idea';

    // Статусы
    const STATUS_BACKLOG = 'backlog';
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_REVIEW = 'review';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Приоритеты
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Видимость
    const VISIBILITY_PERSONAL = 'personal';
    const VISIBILITY_DEPARTMENT = 'department';
    const VISIBILITY_COMPANY = 'company';

    // Связи
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function comments()
    {
        return $this->hasMany(TodoComment::class)->orderBy('created_at', 'desc');
    }

    public function subtasks()
    {
        return $this->hasMany(TodoSubtask::class)->orderBy('created_at');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'todo_participants')->withTimestamps();
    }

    // Скоупы
    public function scopePersonal($query)
    {
        return $query->where('visibility', self::VISIBILITY_PERSONAL);
    }

    public function scopeShared($query)
    {
        return $query->whereIn('visibility', [self::VISIBILITY_DEPARTMENT, self::VISIBILITY_COMPANY]);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('created_by', $userId)
                ->orWhere('assigned_to', $userId)
                ->orWhereHas('participants', function ($q2) use ($userId) {
                    $q2->where('user_id', $userId);
                });
        });
    }

    // Хелперы
    public function getStatusNameAttribute()
    {
        $map = [
            self::STATUS_BACKLOG => 'Бэклог',
            self::STATUS_PENDING => 'Ожидает',
            self::STATUS_IN_PROGRESS => 'В работе',
            self::STATUS_REVIEW => 'На проверке',
            self::STATUS_COMPLETED => 'Выполнена',
            self::STATUS_CANCELLED => 'Отменена',
        ];
        return $map[$this->status] ?? $this->status;
    }

    public function getPriorityNameAttribute()
    {
        $map = [
            self::PRIORITY_LOW => 'Низкий',
            self::PRIORITY_MEDIUM => 'Средний',
            self::PRIORITY_HIGH => 'Высокий',
            self::PRIORITY_URGENT => 'Срочный',
        ];
        return $map[$this->priority] ?? $this->priority;
    }

    public function getTypeNameAttribute()
    {
        $map = [
            self::TYPE_TASK => 'Задача',
            self::TYPE_URGENT => 'Срочная',
            self::TYPE_REMINDER => 'Напоминание',
            self::TYPE_IDEA => 'Идея',
        ];
        return $map[$this->type] ?? $this->type;
    }

    public function getVisibilityNameAttribute()
    {
        $map = [
            self::VISIBILITY_PERSONAL => 'Личная',
            self::VISIBILITY_DEPARTMENT => 'Отдел',
            self::VISIBILITY_COMPANY => 'Компания',
        ];
        return $map[$this->visibility] ?? $this->visibility;
    }

    public function isOverdue()
    {
        if (!$this->due_date) return false;
        return $this->due_date < now() && !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }
}
