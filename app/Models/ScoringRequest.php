<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoringRequest extends Model
{
    protected $table = 'scoring_requests';

    protected $fillable = [
        'sheet_id',
        'request_number',
        'counterparty',
        'manager_name'
    ];

    protected $appends = ['total_points'];

    // Связь с ведомостью - проверьте, что внешний ключ правильный
    public function sheet(): BelongsTo
    {
        return $this->belongsTo(ScoringSheet::class, 'sheet_id', 'id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ScoringVariant::class, 'request_id', 'id')->orderBy('sort_order');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ScoringEntry::class, 'request_id', 'id');
    }

    public function getTotalPointsAttribute(): float
    {
        return (float) $this->variants->sum(function($variant) {
            return $variant->entries->sum('points');
        });
    }
}
