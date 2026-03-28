<?php
// app/Models/Task.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'uuid', 'title', 'description', 'type', 'status', 'priority',
        'visibility', 'allowed_roles', 'allowed_departments',
        'due_date', 'reminder_at', 'reminder_sent', 'started_at', 'completed_at',
        'recurrence_pattern', 'recurrence_settings', 'parent_task_id',
        'progress', 'metadata', 'created_by', 'assigned_to',
        'department_id', 'project_id'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reminder_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_sent' => 'boolean',
        'allowed_roles' => 'array',
        'allowed_departments' => 'array',
        'recurrence_settings' => 'array',
        'metadata' => 'array',
        'progress' => 'integer',
    ];

    // Константы для типов
    const TYPE_TASK = 'task';
    const TYPE_URGENT = 'urgent';
    const TYPE_REMINDER = 'reminder';
    const TYPE_IDEA = 'idea';
    const TYPE_BUG = 'bug';
    const TYPE_FEATURE = 'feature';

    // Константы для статусов
    const STATUS_BACKLOG = 'backlog';
    const STATUS_TODO = 'todo';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ARCHIVED = 'archived';

    // Константы для приоритетов
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    // Константы для видимости
    const VISIBILITY_PERSONAL = 'personal';
    const VISIBILITY_DEPARTMENT = 'department';
    const VISIBILITY_COMPANY = 'company';
    const VISIBILITY_PROJECT = 'project';

    // Связи
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_participants')
            ->withPivot('role', 'permissions')
            ->withTimestamps();
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(TaskSubtask::class)->orderBy('order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function history(): HasMany
    {
        return $this->hasMany(TaskHistory::class)->latest();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tag', 'task_id', 'tag_id')
            ->withTimestamps();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    // Скоупы для оптимизации запросов
    public function scopeVisibleTo($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id)
                ->orWhereHas('participants', function ($q2) use ($user) {
                    $q2->where('user_id', $user->id);
                })
                ->orWhere(function ($q3) use ($user) {
                    $q3->where('visibility', self::VISIBILITY_DEPARTMENT)
                        ->where('department_id', $user->department_id);
                })
                ->orWhere('visibility', self::VISIBILITY_COMPANY);
        });
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeByPriority($query, $priority)
    {
        if ($priority !== 'all') {
            return $query->where('priority', $priority);
        }
        return $query;
    }

    // Helpers
    public function isOverdue(): bool
    {
        return $this->due_date &&
            $this->due_date < now() &&
            !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_BACKLOG => 'bg-gray-100 text-gray-800',
            self::STATUS_TODO => 'bg-blue-100 text-blue-800',
            self::STATUS_IN_PROGRESS => 'bg-yellow-100 text-yellow-800',
            self::STATUS_IN_REVIEW => 'bg-purple-100 text-purple-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'bg-gray-100 text-gray-800',
            self::PRIORITY_MEDIUM => 'bg-blue-100 text-blue-800',
            self::PRIORITY_HIGH => 'bg-orange-100 text-orange-800',
            self::PRIORITY_URGENT => 'bg-red-100 text-red-800',
            self::PRIORITY_CRITICAL => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // Query Builder для сложных фильтров
    public static function queryBuilder()
    {
        return QueryBuilder::for(Task::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('priority'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('visibility'),
                AllowedFilter::exact('created_by'),
                AllowedFilter::exact('assigned_to'),
                AllowedFilter::exact('department_id'),
                AllowedFilter::exact('project_id'),
                AllowedFilter::scope('overdue'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('title', 'like', "%{$value}%")
                        ->orWhere('description', 'like', "%{$value}%");
                }),
            ])
            ->allowedSorts([
                'created_at', 'updated_at', 'due_date', 'priority', 'status'
            ])
            ->defaultSort('-priority', 'due_date');
    }
}
