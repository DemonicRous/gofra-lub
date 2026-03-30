<?php
// app/Models/Manager.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $fillable = [
        'last_name',
        'first_name',
        'patronymic',
        'full_name',
        'short_name',
        'position',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($manager) {
            $manager->full_name = trim($manager->last_name . ' ' . $manager->first_name . ' ' . ($manager->patronymic ?? ''));
            $manager->short_name = $manager->last_name . ' ' . mb_substr($manager->first_name, 0, 1) . '.';
            if ($manager->patronymic) {
                $manager->short_name .= mb_substr($manager->patronymic, 0, 1) . '.';
            }
        });

        static::updating(function ($manager) {
            $manager->full_name = trim($manager->last_name . ' ' . $manager->first_name . ' ' . ($manager->patronymic ?? ''));
            $manager->short_name = $manager->last_name . ' ' . mb_substr($manager->first_name, 0, 1) . '.';
            if ($manager->patronymic) {
                $manager->short_name .= mb_substr($manager->patronymic, 0, 1) . '.';
            }
        });
    }
}
