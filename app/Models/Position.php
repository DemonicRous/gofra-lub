<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'department_id',
        'level',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Связь с отделом
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Связь с пользователями
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Активные пользователи на этой должности
    public function activeUsers()
    {
        return $this->users()->whereNotNull('approved_at');
    }

    // Получить уровень на русском
    public function getLevelNameAttribute()
    {
        $levels = [
            'junior' => 'Младший специалист',
            'middle' => 'Специалист',
            'senior' => 'Старший специалист',
            'lead' => 'Ведущий специалист',
            'head' => 'Руководитель',
        ];
        return $levels[$this->level] ?? $this->level;
    }

}
