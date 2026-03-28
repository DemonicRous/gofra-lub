<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskSubtask extends Model
{
    protected $fillable = ['title', 'is_completed', 'task_id', 'order'];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
