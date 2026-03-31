<?php
// app/Services/Scoring/SheetService.php

namespace App\Services\Scoring;

use App\Models\User;
use App\Models\ScoringSheet;
use App\Models\ScoringRequest;
use App\Models\ScoringVariant;
use App\Models\ScoringEntry;
use App\Models\ScoringCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Создание новой заявки с вариантами
     */
    public function createRequest(ScoringSheet $sheet, array $data): ScoringRequest
    {
        if (!$sheet->isDraft()) {
            throw new \Exception('Нельзя добавлять записи в подтвержденную ведомость');
        }

        return DB::transaction(function () use ($sheet, $data) {
            // Создаем заявку
            $request = $sheet->requests()->create([
                'request_number' => $data['request_number'] ?? null,
                'counterparty' => $data['counterparty'] ?? null,
                'manager_name' => $data['manager_name'] ?? null,
            ]);

            // Создаем варианты
            foreach ($data['variants'] as $index => $variantData) {
                $variant = $request->variants()->create([
                    'name' => $variantData['name'] ?? "Вариант " . ($index + 1),
                    'sort_order' => $index,
                ]);

                // Создаем записи для каждого выбранного пункта в варианте
                foreach ($variantData['category_ids'] as $categoryId) {
                    $category = ScoringCategory::with('parent')->findOrFail($categoryId);
                    $parent = $category->parent;

                    // Рассчитываем баллы
                    $basePoints = $parent ? $parent->base_points : 0;
                    $additionalPoints = $category->points;
                    $points = $basePoints + $additionalPoints;

                    $entry = $variant->entries()->create([
                        'sheet_id' => $sheet->id,
                        'request_id' => $request->id,
                        'category_id' => $category->id,
                        'quantity' => 1,
                        'points' => $points,
                        'metadata' => [
                            'parent_name' => $parent ? $parent->name : null,
                            'parent_id' => $parent ? $parent->id : null,
                            'category_name' => $category->name,
                            'base_points' => $basePoints,
                            'additional_points' => $additionalPoints,
                        ]
                    ]);
                }
            }

            $sheet->recalculateTotal();

            return $request;
        });
    }

    /**
     * Добавить вариант к существующей заявке
     */
    public function addVariantToRequest(ScoringRequest $request, array $data): ScoringVariant
    {
        $sheet = $request->sheet;

        if (!$sheet->isDraft()) {
            throw new \Exception('Нельзя добавлять варианты в подтвержденную ведомость');
        }

        return DB::transaction(function () use ($request, $data, $sheet) {
            $maxOrder = $request->variants()->max('sort_order') ?? -1;

            $variant = $request->variants()->create([
                'name' => $data['name'] ?? "Вариант " . ($maxOrder + 2),
                'sort_order' => $maxOrder + 1,
            ]);

            foreach ($data['category_ids'] as $categoryId) {
                $category = ScoringCategory::with('parent')->findOrFail($categoryId);
                $parent = $category->parent;

                $basePoints = $parent ? $parent->base_points : 0;
                $additionalPoints = $category->points;
                $points = $basePoints + $additionalPoints;

                $variant->entries()->create([
                    'sheet_id' => $sheet->id,
                    'request_id' => $request->id,
                    'category_id' => $category->id,
                    'quantity' => 1,
                    'points' => $points,
                    'metadata' => [
                        'parent_name' => $parent ? $parent->name : null,
                        'parent_id' => $parent ? $parent->id : null,
                        'category_name' => $category->name,
                        'base_points' => $basePoints,
                        'additional_points' => $additionalPoints,
                    ]
                ]);
            }

            $sheet->recalculateTotal();

            return $variant;
        });
    }

    /**
     * Получить заявки с вариантами
     */
    public function getRequestsWithVariants(ScoringSheet $sheet): array
    {
        return $sheet->requests()
            ->with(['variants.entries.category', 'variants.entries.category.parent'])
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'request_number' => $request->request_number,
                    'counterparty' => $request->counterparty,
                    'manager_name' => $request->manager_name,
                    'total_points' => $request->total_points,
                    'variants' => $request->variants->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'name' => $variant->name,
                            'total_points' => $variant->total_points,
                            'entries' => $variant->entries->map(function ($entry) {
                                return [
                                    'id' => $entry->id,
                                    'category_name' => $entry->category->name,
                                    'parent_name' => $entry->category->parent->name ?? null,
                                    'points' => $entry->points,
                                    'metadata' => $entry->metadata,
                                ];
                            }),
                        ];
                    }),
                ];
            })
            ->toArray();
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
