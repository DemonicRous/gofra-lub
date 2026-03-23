<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'head_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Связь с должностями
    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    // Связь с пользователями
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Руководитель отдела
    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    // Активные должности
    public function activePositions()
    {
        return $this->positions()->where('is_active', true);
    }

    // Активные пользователи
    public function activeUsers()
    {
        return $this->users()->whereNotNull('approved_at');
    }
}
