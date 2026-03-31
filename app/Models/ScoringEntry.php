<?php
// app/Models/ScoringEntry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoringEntry extends Model
{
    protected $table = 'scoring_entries';

    protected $fillable = [
        'sheet_id', 'request_id', 'variant_id', 'category_id',
        'quantity', 'points', 'notes', 'metadata'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'points' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(ScoringSheet::class, 'sheet_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ScoringRequest::class, 'request_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ScoringVariant::class, 'variant_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ScoringCategory::class, 'category_id');
    }
}
