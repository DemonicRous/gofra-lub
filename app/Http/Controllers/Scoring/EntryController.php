<?php
// app/Http/Controllers/Scoring/EntryController.php

namespace App\Http\Controllers\Scoring;

use App\Http\Controllers\Controller;
use App\Services\Scoring\SheetService;
use App\Models\ScoringSheet;
use App\Models\ScoringCategory;
use App\Models\Manager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EntryController extends Controller
{
    protected $sheetService;

    public function __construct(SheetService $sheetService)
    {
        $this->sheetService = $sheetService;
    }

    /**
     * Форма создания записи
     */
    public function create(ScoringSheet $sheet, Request $request)
    {
        // Проверяем доступ
        if (!$this->canEditSheet($sheet, $request->user())) {
            abort(403, 'У вас нет доступа к этой ведомости');
        }

        if (!$sheet->isDraft()) {
            abort(403, 'Нельзя добавлять записи в подтвержденную ведомость');
        }

        // Получаем тип подотдела пользователя
        $departmentType = $sheet->user->scoring_department ?? 'constructor';

        \Log::info('=== EntryController create ===');
        \Log::info('Sheet ID: ' . $sheet->id);
        \Log::info('User ID: ' . $request->user()->id);
        \Log::info('Department type: ' . $departmentType);

        // Получаем родительские категории с дочерними
        $categories = ScoringCategory::with(['children' => function($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])
            ->where('type', $departmentType)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        \Log::info('Raw categories count: ' . $categories->count());

        // Форматируем категории для фронтенда
        $formattedCategories = $categories->map(function ($parent) {
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

        \Log::info('Formatted categories count: ' . $formattedCategories->count());

        // Получаем список менеджеров
        $managers = Manager::where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'last_name', 'first_name', 'patronymic', 'full_name', 'short_name']);

        \Log::info('Managers count: ' . $managers->count());

        return Inertia::render('Scoring/EntryForm', [
            'sheet' => $sheet,
            'categories' => $formattedCategories,
            'managers' => $managers,
        ]);
    }

    /**
     * Сохранение записи
     */
    public function store(ScoringSheet $sheet, Request $request)
    {
        // Проверяем доступ
        if (!$this->canEditSheet($sheet, $request->user())) {
            abort(403, 'У вас нет доступа к этой ведомости');
        }

        if (!$sheet->isDraft()) {
            return redirect()->back()->with('error', 'Нельзя добавлять записи в подтвержденную ведомость');
        }

        \Log::info('=== EntryController store ===');
        \Log::info('Request data: ' . json_encode($request->all()));

        $validated = $request->validate([
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:scoring_categories,id',
            'request_number' => 'nullable|string|max:100',
            'counterparty' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'variants' => 'nullable|array',
            'variants.*.name' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $entry = $this->sheetService->addEntry($sheet, $validated);

            return redirect()->route('scoring.sheets.show', $sheet)
                ->with('success', 'Запись успешно добавлена');
        } catch (\Exception $e) {
            \Log::error('Error adding entry: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Удаление записи
     */
    public function destroy($entryId, Request $request)
    {
        $entry = \App\Models\ScoringEntry::with('sheet')->findOrFail($entryId);
        $sheet = $entry->sheet;

        // Проверяем доступ
        if (!$this->canEditSheet($sheet, $request->user())) {
            abort(403, 'У вас нет доступа к этой ведомости');
        }

        if (!$sheet->isDraft()) {
            return redirect()->back()->with('error', 'Нельзя удалять записи из подтвержденной ведомости');
        }

        $entry->delete();
        $sheet->recalculateTotal();

        return redirect()->back()->with('success', 'Запись удалена');
    }

    /**
     * Проверка права на редактирование ведомости
     */
    private function canEditSheet(ScoringSheet $sheet, $user): bool
    {
        return $sheet->user_id === $user->id && $sheet->isDraft();
    }
}
