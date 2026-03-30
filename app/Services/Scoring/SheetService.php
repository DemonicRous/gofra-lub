<?php
// app/Services/Scoring/SheetService.php

namespace App\Services\Scoring;

use App\Models\User;
use App\Models\ScoringSheet;
use App\Models\ScoringEntry;
use App\Models\ScoringCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SheetService
{
    /**
     * Создать ведомость для пользователя на указанный месяц
     */
    public function createSheetForUser(User $user, Carbon $date): ScoringSheet
    {
        return ScoringSheet::firstOrCreate(
            [
                'user_id' => $user->id,
                'period_date' => $date->copy()->startOfMonth(),
            ],
            [
                'status' => ScoringSheet::STATUS_DRAFT,
                'total_points' => 0,
            ]
        );
    }

    /**
     * Создать ведомости для всех сотрудников на следующий месяц
     */
    public function createSheetsForNextMonth(): void
    {
        $nextMonth = Carbon::now()->addMonth()->startOfMonth();

        // Получаем всех активных сотрудников Отдела развития
        $users = User::whereNotNull('scoring_department')
            ->whereNotNull('approved_at')
            ->get();

        foreach ($users as $user) {
            $this->createSheetForUser($user, $nextMonth);
        }
    }

    /**
     * Создать ведомости для всех сотрудников на указанный месяц
     */
    public function createSheetsForMonth(Carbon $date): int
    {
        $users = User::whereNotNull('scoring_department')
            ->whereNotNull('approved_at')
            ->get();

        $created = 0;

        foreach ($users as $user) {
            $sheet = $this->createSheetForUser($user, $date);
            if ($sheet->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * Создать ведомость для нового пользователя
     */
    public function createSheetForNewUser(User $user): void
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $this->createSheetForUser($user, $currentMonth);
    }

    /**
     * Подтверждение ведомости сотрудником
     */
    public function confirmSheet(ScoringSheet $sheet, User $user): void
    {
        if ($sheet->user_id !== $user->id) {
            throw new \Exception('Вы можете подтвердить только свою ведомость');
        }

        if (!$sheet->isDraft()) {
            throw new \Exception('Ведомость уже подтверждена');
        }

        $sheet->confirm();
    }

    /**
     * Утверждение ведомости руководителем
     */
    public function approveSheet(ScoringSheet $sheet, User $approver): void
    {
        if (!$sheet->isConfirmed()) {
            throw new \Exception('Можно утверждать только подтвержденные ведомости');
        }

        $sheet->approve($approver);
    }

    /**
     * Добавить запись в ведомость с учетом базовых баллов категорий
     */
    public function addEntry(ScoringSheet $sheet, array $data): ScoringEntry
    {
        if (!$sheet->isDraft()) {
            throw new \Exception('Нельзя добавлять записи в подтвержденную ведомость');
        }

        return DB::transaction(function () use ($sheet, $data) {
            // Получаем все выбранные подкатегории
            $categories = ScoringCategory::with('parent')
                ->whereIn('id', $data['category_ids'])
                ->get();

            $totalPoints = 0;
            $createdEntry = null;
            $groupedByParent = [];

            // Группируем по родительским категориям
            foreach ($categories as $category) {
                $parentId = $category->parent_id;
                if (!isset($groupedByParent[$parentId])) {
                    $groupedByParent[$parentId] = [
                        'parent' => $category->parent,
                        'children' => []
                    ];
                }
                $groupedByParent[$parentId]['children'][] = $category;
            }

            // Для каждой родительской категории создаем запись
            foreach ($groupedByParent as $parentId => $group) {
                $parent = $group['parent'];
                $children = $group['children'];

                // Рассчитываем баллы: базовые баллы родителя + сумма дополнительных баллов подкатегорий
                $points = $parent->base_points * $data['quantity'];
                foreach ($children as $child) {
                    $points += $child->points * $data['quantity'];
                }

                $totalPoints += $points;

                // Создаем запись для родительской категории
                $entry = $sheet->entries()->create([
                    'category_id' => $parent->id,
                    'request_number' => $data['request_number'] ?? null,
                    'counterparty' => $data['counterparty'] ?? null,
                    'manager_name' => $data['manager_name'] ?? null,
                    'quantity' => $data['quantity'] ?? 1,
                    'notes' => $data['notes'] ?? null,
                    'points' => $points,
                    'metadata' => [
                        'selected_children' => $children->pluck('id')->toArray(),
                        'selected_children_names' => $children->pluck('name')->toArray(),
                        'base_points' => $parent->base_points,
                        'additional_points' => $children->sum('points') * $data['quantity'],
                    ]
                ]);

                $createdEntry = $entry;

                // Добавляем варианты, если есть
                if (!empty($data['variants'])) {
                    foreach ($data['variants'] as $index => $variant) {
                        if (!empty($variant['name'])) {
                            $entry->variants()->create([
                                'name' => $variant['name'],
                                'quantity' => $variant['quantity'] ?? 1,
                                'points' => $variant['points'] ?? 0,
                                'sort_order' => $index,
                            ]);
                        }
                    }
                }
            }

            // Обновляем общую сумму в ведомости
            $sheet->recalculateTotal();

            return $createdEntry;
        });
    }

    /**
     * Обновить запись
     */
    public function updateEntry(ScoringEntry $entry, array $data): ScoringEntry
    {
        $sheet = $entry->sheet;

        if (!$sheet->isDraft()) {
            throw new \Exception('Нельзя редактировать записи в подтвержденной ведомости');
        }

        return DB::transaction(function () use ($entry, $data, $sheet) {
            $entry->update([
                'request_number' => $data['request_number'] ?? $entry->request_number,
                'counterparty' => $data['counterparty'] ?? $entry->counterparty,
                'manager_name' => $data['manager_name'] ?? $entry->manager_name,
                'quantity' => $data['quantity'] ?? $entry->quantity,
                'notes' => $data['notes'] ?? $entry->notes,
            ]);

            // Обновляем баллы
            $entry->calculatePoints();
            $sheet->recalculateTotal();

            return $entry;
        });
    }

    /**
     * Получить статистику по подотделу за период
     */
    public function getDepartmentStats(string $department, Carbon $date): array
    {
        $sheets = ScoringSheet::whereHas('user', function ($query) use ($department) {
            $query->where('scoring_department', $department);
        })
            ->whereYear('period_date', $date->year)
            ->whereMonth('period_date', $date->month)
            ->with('user')
            ->get();

        return [
            'total_points' => $sheets->sum('total_points'),
            'confirmed_count' => $sheets->where('status', ScoringSheet::STATUS_CONFIRMED)->count(),
            'approved_count' => $sheets->where('status', ScoringSheet::STATUS_APPROVED)->count(),
            'draft_count' => $sheets->where('status', ScoringSheet::STATUS_DRAFT)->count(),
            'sheets' => $sheets,
        ];
    }

    /**
     * Получить сводку по всем отделам
     */
    public function getSummary(Carbon $date): array
    {
        return [
            'constructor' => $this->getDepartmentStats('constructor', $date),
            'designer' => $this->getDepartmentStats('designer', $date),
        ];
    }

    /**
     * Получить статистику по ведомостям
     */
    public function getStatistics(): array
    {
        return [
            'total' => ScoringSheet::count(),
            'draft' => ScoringSheet::where('status', ScoringSheet::STATUS_DRAFT)->count(),
            'confirmed' => ScoringSheet::where('status', ScoringSheet::STATUS_CONFIRMED)->count(),
            'approved' => ScoringSheet::where('status', ScoringSheet::STATUS_APPROVED)->count(),
            'by_month' => ScoringSheet::selectRaw('DATE_FORMAT(period_date, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ];
    }
}
