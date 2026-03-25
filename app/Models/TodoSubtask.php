<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TodoSubtask extends Model
{
    protected $fillable = ['title', 'is_completed', 'todo_id'];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function todo()
    {
        return $this->belongsTo(Todo::class);
    }
}
