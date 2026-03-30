<?php
// app/Models/ScoringSheet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoringSheet extends Model
{
    protected $fillable = [
        'user_id', 'period_date', 'status', 'total_points',
        'confirmed_at', 'approved_by', 'notes'
    ];

    protected $casts = [
        'period_date' => 'date',
        'confirmed_at' => 'datetime',
        'total_points' => 'decimal:2',
    ];

    // Константы статусов
    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_APPROVED = 'approved';

    // Связи
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ScoringEntry::class, 'sheet_id');
    }

    // Скоупы
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForPeriod($query, $year, $month)
    {
        return $query->whereYear('period_date', $year)->whereMonth('period_date', $month);
    }

    // Помощники
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function confirm(): void
    {
        $this->status = self::STATUS_CONFIRMED;
        $this->confirmed_at = now();
        $this->save();
    }

    public function approve(User $approver): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approver->id;
        $this->save();
    }

    public function recalculateTotal(): void
    {
        $this->total_points = $this->entries()->sum('points');
        $this->save();
    }

    // Получение названия месяца
    public function getMonthNameAttribute(): string
    {
        return $this->period_date->translatedFormat('F Y');
    }
}
