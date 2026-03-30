<?php
// app/Models/ScoringCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoringCategory extends Model
{
    protected $fillable = [
        'name', 'description', 'type', 'base_points', 'points', 'unit',
        'parent_id', 'is_multiselect', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'base_points' => 'decimal:2',
        'points' => 'decimal:2',
        'is_multiselect' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Связи
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ScoringCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ScoringCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ScoringEntry::class, 'category_id');
    }

    // Скоупы
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Помощники
    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' → ' . $this->name;
        }
        return $this->name;
    }

    /**
     * Получить полные баллы за категорию (базовые + дополнительные)
     * Для дочерних категорий возвращает base_points родителя + points
     */
    public function getTotalPointsAttribute(): float
    {
        if ($this->parent) {
            return $this->parent->base_points + $this->points;
        }
        return $this->base_points;
    }

    /**
     * Получить форматированное описание баллов для отображения
     */
    public function getPointsDescriptionAttribute(): string
    {
        if ($this->parent) {
            $total = $this->total_points;
            $base = $this->parent->base_points;

            if ($base > 0 && $this->points > 0) {
                return "{$base} + {$this->points} = {$total} баллов";
            } elseif ($base > 0) {
                return "{$total} баллов (базовые)";
            } else {
                return "{$total} баллов";
            }
        }
        return "{$this->base_points} баллов (базовые)";
    }
}
