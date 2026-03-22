<?php

namespace App\Models;

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
        'position',
        'department',
        'email',
        'password',
        'approved_at',
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

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    /**
     * Генерация никнейма из email
     */
    public static function generateNicknameFromEmail(string $email): string
    {
        // Извлекаем часть до @
        $localPart = explode('@', $email)[0];

        // Заменяем точки на точки (можно оставить как есть)
        // Если нужно удалить точки, раскомментируйте следующую строку
        // $localPart = str_replace('.', '', $localPart);

        // Делаем никнейм уникальным, если такой уже существует
        $nickname = $localPart;
        $counter = 1;

        while (self::where('nickname', $nickname)->exists()) {
            $nickname = $localPart . $counter;
            $counter++;
        }

        return $nickname;
    }

    /**
     * Получение полного имени
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->last_name . ' ' . $this->first_name . ' ' . $this->patronymic);
    }

    /**
     * Получение короткого имени (Фамилия И.О.)
     */
    public function getShortNameAttribute(): string
    {
        $shortName = $this->last_name . ' ' . mb_substr($this->first_name, 0, 1) . '.';

        if ($this->patronymic) {
            $shortName .= mb_substr($this->patronymic, 0, 1) . '.';
        }

        return $shortName;
    }
}
