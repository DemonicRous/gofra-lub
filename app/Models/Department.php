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
        'parent_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Родительский отдел
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    // Дочерние отделы
    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

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

    // Получить все дочерние отделы рекурсивно
    public function getAllChildren()
    {
        $children = collect();
        foreach ($this->children as $child) {
            $children->push($child);
            $children = $children->merge($child->getAllChildren());
        }
        return $children;
    }

    // Получить всех пользователей из отдела и всех подотделов
    public function getAllUsers()
    {
        $departmentIds = collect([$this->id])->merge($this->getAllChildren()->pluck('id'));
        return User::whereIn('department_id', $departmentIds)->get();
    }
}
