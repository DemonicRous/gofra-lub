<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScoringVariant extends Model
{
    protected $table = 'scoring_variants';

    protected $fillable = [
        'request_id',
        'name',
        'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $appends = ['total_points'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ScoringRequest::class, 'request_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(ScoringEntry::class, 'variant_id');
    }

    public function getTotalPointsAttribute(): float
    {
        return (float) $this->entries->sum('points');
    }
}
