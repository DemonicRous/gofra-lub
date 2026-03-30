<?php
// app/Http/Controllers/Scoring/SheetController.php

namespace App\Http\Controllers\Scoring;

use App\Http\Controllers\Controller;
use App\Services\Scoring\SheetService;
use App\Models\ScoringSheet;
use App\Models\ScoringCategory;  // Правильный импорт
use App\Models\Manager;           // Правильный импорт
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SheetController extends Controller
{
    protected $sheetService;

    public function __construct(SheetService $sheetService)
    {
        $this->sheetService = $sheetService;
    }

    /**
     * Список ведомостей пользователя
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $sheets = ScoringSheet::where('user_id', $user->id)
            ->orderBy('period_date', 'desc')
            ->paginate(12);

        return Inertia::render('Scoring/Index', [
            'sheets' => $sheets,
            'currentSheet' => $this->getCurrentSheet($user),
        ]);
    }

    /**
     * Просмотр ведомости
     */
    public function show(ScoringSheet $sheet, Request $request)
    {
        // Проверяем доступ
        if (!$this->canViewSheet($sheet, $request->user())) {
            abort(403, 'У вас нет доступа к этой ведомости');
        }

        $sheet->load([
            'entries.category.parent',
            'entries.variants',
            'user',
        ]);

        // Добавляем количество записей для отображения
        $sheet->entries_count = $sheet->entries->count();

        // Получаем тип подотдела пользователя
        $departmentType = $sheet->user->scoring_department ?? 'constructor';

        // Получаем категории для формы добавления записи
        $categories = ScoringCategory::with(['children' => function($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])
            ->where('type', $departmentType)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($parent) {
                return [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'base_points' => (float) $parent->base_points,
                    'is_multiselect' => (bool) $parent->is_multiselect,
                    'children' => $parent->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'points' => (float) $child->points,
                            'unit' => $child->unit,
                        ];
                    }),
                ];
            });

        // Получаем менеджеров
        $managers = Manager::where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'last_name', 'first_name', 'patronymic', 'full_name', 'short_name']);

        return Inertia::render('Scoring/Sheet', [
            'sheet' => $sheet,
            'isEditable' => $sheet->isDraft() && $sheet->user_id === $request->user()->id,
            'categories' => $categories,
            'managers' => $managers,
        ]);
    }

    /**
     * Получить текущую ведомость
     */
    private function getCurrentSheet($user)
    {
        $currentMonth = Carbon::now()->startOfMonth();

        return ScoringSheet::firstOrCreate(
            [
                'user_id' => $user->id,
                'period_date' => $currentMonth,
            ],
            [
                'status' => ScoringSheet::STATUS_DRAFT,
            ]
        );
    }

    /**
     * Проверка доступа к ведомости
     */
    private function canViewSheet(ScoringSheet $sheet, $user): bool
    {
        if ($sheet->user_id === $user->id) {
            return true;
        }

        if ($user->hasRole('manager') || $user->hasRole('admin')) {
            return $sheet->user->scoring_department === $user->scoring_department;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return false;
    }

    /**
     * Подтверждение ведомости
     */
    public function confirm(ScoringSheet $sheet, Request $request)
    {
        if ($sheet->user_id !== $request->user()->id) {
            return redirect()->back()->with('error', 'Вы можете подтвердить только свою ведомость');
        }

        if (!$sheet->isDraft()) {
            return redirect()->back()->with('error', 'Ведомость уже подтверждена');
        }

        try {
            $this->sheetService->confirmSheet($sheet, $request->user());
            return redirect()->back()->with('success', 'Ведомость успешно подтверждена');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
