<?php
// app/Policies/ScoringSheetPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\ScoringSheet;

class ScoringSheetPolicy
{
    /**
     * Просмотр ведомости
     */
    public function view(User $user, ScoringSheet $sheet): bool
    {
        // Сотрудник видит свою ведомость
        if ($sheet->user_id === $user->id) {
            return true;
        }

        // Руководитель видит ведомости своего подотдела
        if ($user->hasRole('manager') || $user->hasRole('admin')) {
            return $sheet->user->scoring_department === $user->scoring_department;
        }

        // Администратор видит все
        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Редактирование ведомости
     */
    public function update(User $user, ScoringSheet $sheet): bool
    {
        // Только создатель может редактировать черновик
        if ($sheet->user_id === $user->id && $sheet->isDraft()) {
            return true;
        }

        return false;
    }

    /**
     * Подтверждение ведомости
     */
    public function confirm(User $user, ScoringSheet $sheet): bool
    {
        // Только создатель может подтвердить черновик
        return $sheet->user_id === $user->id && $sheet->isDraft();
    }

    /**
     * Утверждение ведомости (руководитель)
     */
    public function approve(User $user, ScoringSheet $sheet): bool
    {
        // Руководитель или администратор может утвердить подтвержденную ведомость
        if (($user->hasRole('manager') || $user->hasRole('admin')) && $sheet->isConfirmed()) {
            return true;
        }

        return false;
    }
}
