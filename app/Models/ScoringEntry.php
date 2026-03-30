<?php
// app/Models/ScoringEntry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoringEntry extends Model
{
    protected $fillable = [
        'sheet_id', 'category_id', 'request_number', 'counterparty',
        'manager_name', 'quantity', 'points', 'notes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'points' => 'decimal:2',
    ];

    // Связи
    public function sheet(): BelongsTo
    {
        return $this->belongsTo(ScoringSheet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ScoringCategory::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ScoringVariant::class, 'entry_id')->orderBy('sort_order');
    }

    // Помощники
    public function calculatePoints(): void
    {
        $basePoints = $this->category->points * $this->quantity;
        $variantsPoints = $this->variants()->sum('points');
        $this->points = $basePoints + $variantsPoints;
        $this->save();

        // Обновляем общую сумму в ведомости
        $this->sheet->recalculateTotal();
    }

    public function getCategoryFullNameAttribute(): string
    {
        return $this->category->full_name;
    }

    public function getPointsFormattedAttribute(): string
    {
        return number_format($this->points, 2, '.', '');
    }
}
