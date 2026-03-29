<?php
// app/Models/Audit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Audit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'audits';

    protected $fillable = [
        'uuid', 'title', 'description', 'findings', 'recommendations',
        'client_name', 'client_contact', 'address', 'object_name',
        'status', 'audit_type', 'audit_date', 'start_time', 'end_time',
        'completed_at', 'latitude', 'longitude', 'signature_data', 'signed_at',
        'created_by', 'assigned_to', 'department_id', 'related_task_id'
    ];

    protected $casts = [
        'audit_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'completed_at' => 'datetime',
        'signed_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'array'
    ];

    // Константы статусов
    const STATUS_DRAFT = 'draft';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Константы типов аудита
    const TYPE_MEASUREMENT = 'measurement';
    const TYPE_PRODUCTION_LINE = 'production_line';
    const TYPE_QUALITY_CHECK = 'quality_check';
    const TYPE_CONSULTATION = 'consultation';
    const TYPE_OTHER = 'other';

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

    public function relatedTask()
    {
        return $this->belongsTo(Task::class, 'related_task_id');
    }

    public function media()
    {
        return $this->hasMany(AuditMedia::class)->orderBy('sort_order');
    }

    public function photos()
    {
        return $this->hasMany(AuditMedia::class)->where('media_type', 'photo')->orderBy('sort_order');
    }

    public function comments()
    {
        return $this->hasMany(AuditComment::class)->latest();
    }

    // Скоупы
    public function scopeVisibleTo($query, $user)
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id)
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('department_id', $user->department_id);
                });
        });
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Помощники
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_IN_PROGRESS]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getStartTimeFormattedAttribute()
    {
        if (!$this->start_time) return null;
        return date('H:i', strtotime($this->start_time));
    }

    public function getEndTimeFormattedAttribute()
    {
        if (!$this->end_time) return null;
        return date('H:i', strtotime($this->end_time));
    }

    public function getAuditDateFormattedAttribute()
    {
        if (!$this->audit_date) return null;
        return $this->audit_date->format('Y-m-d');
    }

    // Доступные статусы
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_IN_PROGRESS => 'В процессе',
            self::STATUS_COMPLETED => 'Завершен',
            self::STATUS_CANCELLED => 'Отменен'
        ];
    }

    // Смена статуса на "В процессе"
    public function markAsInProgress(): void
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->save();
    }

    // Смена статуса на "Завершен"
    public function markAsCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        $this->save();
    }

}
