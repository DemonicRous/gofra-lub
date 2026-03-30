<?php
// app/Http/Controllers/Admin/ScoringCategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Scoring\CategoryService;
use App\Models\ScoringCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScoringCategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Управление категориями
     */
    public function index(Request $request)
    {
        $categories = ScoringCategory::with('parent')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get();

        // Получаем категории в виде дерева для отображения
        $tree = ScoringCategory::with(['children' => function($query) {
            $query->orderBy('sort_order');
        }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Scoring/Categories', [
            'categories' => $categories->groupBy('type'),
            'tree' => $tree,
        ]);
    }

    /**
     * Создание категории
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:constructor,designer,common',
            'base_points' => 'nullable|numeric|min:0',
            'points' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:scoring_categories,id',
            'is_multiselect' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Если это корневая категория (нет parent_id), сохраняем base_points
        if (empty($validated['parent_id'])) {
            $validated['base_points'] = $validated['base_points'] ?? 0;
            $validated['points'] = 0;
        } else {
            // Если это подкатегория, сохраняем points
            $validated['points'] = $validated['points'] ?? 0;
            $validated['base_points'] = 0;
        }

        // Устанавливаем sort_order по умолчанию
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = 0;
        }

        // Устанавливаем is_active по умолчанию
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $category = ScoringCategory::create($validated);

        // Очищаем кэш
        $this->categoryService->clearCache();

        return redirect()->back()->with('success', 'Категория успешно создана');
    }

    /**
     * Обновление категории
     */
    public function update(ScoringCategory $category, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_points' => 'nullable|numeric|min:0',
            'points' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:scoring_categories,id',
            'is_multiselect' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // Если это корневая категория (нет parent_id), обновляем base_points
        if ($category->parent_id === null) {
            $validated['base_points'] = $validated['base_points'] ?? 0;
            $validated['points'] = 0;
        } else {
            // Если это подкатегория, обновляем points
            $validated['points'] = $validated['points'] ?? 0;
            $validated['base_points'] = 0;
        }

        $category->update($validated);

        // Очищаем кэш
        $this->categoryService->clearCache();

        return redirect()->back()->with('success', 'Категория успешно обновлена');
    }

    /**
     * Удаление категории
     */
    public function destroy(ScoringCategory $category, Request $request)
    {
        try {
            // Проверяем, есть ли у категории дочерние элементы
            if ($category->children()->count() > 0) {
                return redirect()->back()->with('error', 'Нельзя удалить категорию, у которой есть подкатегории');
            }

            // Проверяем, используется ли категория в записях
            if ($category->entries()->count() > 0) {
                return redirect()->back()->with('error', 'Нельзя удалить категорию, которая используется в ведомостях');
            }

            $category->delete();
            $this->categoryService->clearCache();

            return redirect()->back()->with('success', 'Категория успешно удалена');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка при удалении категории: ' . $e->getMessage());
        }
    }

    /**
     * Изменение порядка сортировки
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:scoring_categories,id',
            'categories.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->categories as $categoryData) {
            ScoringCategory::where('id', $categoryData['id'])
                ->update(['sort_order' => $categoryData['sort_order']]);
        }

        $this->categoryService->clearCache();

        return response()->json(['success' => true]);
    }
}
