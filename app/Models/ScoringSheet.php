<?php
// app/Models/ScoringSheet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoringSheet extends Model
{
    protected $table = 'scoring_sheets';

    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'user_id', 'period_date', 'status', 'total_points',
        'confirmed_at', 'approved_by', 'notes'
    ];

    protected $casts = [
        'period_date' => 'date',
        'confirmed_at' => 'datetime',
        'total_points' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ScoringRequest::class, 'sheet_id')->orderBy('created_at', 'desc');
    }

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
        $total = $this->requests()->with('variants.entries')->get()->sum(function ($request) {
            return $request->variants->sum(function ($variant) {
                return $variant->entries->sum('points');
            });
        });

        $this->total_points = $total;
        $this->saveQuietly(); // Используем saveQuietly чтобы избежать рекурсии
    }

    public function getMonthNameAttribute(): string
    {
        return $this->period_date->translatedFormat('F Y');
    }
}
