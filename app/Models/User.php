<?php
// app/Models/User.php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'last_name',
        'first_name',
        'patronymic',
        'nickname',
        'department_id',
        'position_id',
        'email',
        'password',
        'approved_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Связь с отделом
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Связь с должностью
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    public static function generateNicknameFromEmail(string $email): string
    {
        $localPart = explode('@', $email)[0];
        $nickname = $localPart;
        $counter = 1;

        while (self::where('nickname', $nickname)->exists()) {
            $nickname = $localPart . $counter;
            $counter++;
        }

        return $nickname;
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->last_name . ' ' . $this->first_name . ' ' . $this->patronymic);
    }

    public function getShortNameAttribute(): string
    {
        $shortName = $this->last_name . ' ' . mb_substr($this->first_name, 0, 1) . '.';

        if ($this->patronymic) {
            $shortName .= mb_substr($this->patronymic, 0, 1) . '.';
        }

        return $shortName;
    }

    // Добавляем метод для получения имени с должностью
    public function getNameWithPositionAttribute(): string
    {
        $name = $this->short_name;
        if ($this->position) {
            $name .= " ({$this->position->name})";
        }
        return $name;
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    // Связи для задач
    public function tasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function taskParticipations()
    {
        return $this->belongsToMany(Task::class, 'task_participants')
            ->withPivot('role', 'permissions')
            ->withTimestamps();
    }
}
