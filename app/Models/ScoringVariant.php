<?php
// app/Models/ScoringVariant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoringVariant extends Model
{
    protected $fillable = [
        'entry_id', 'name', 'quantity', 'points', 'sort_order'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'points' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ScoringEntry::class);
    }
}
