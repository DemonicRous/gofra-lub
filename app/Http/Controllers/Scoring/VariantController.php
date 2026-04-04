<?php
// app/Http/Controllers/Scoring/VariantController.php

namespace App\Http\Controllers\Scoring;

use App\Http\Controllers\Controller;
use App\Models\ScoringVariant;
use App\Models\ScoringCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VariantController extends Controller
{
    /**
     * Обновление варианта
     */
    public function update(Request $request, ScoringVariant $variant)
    {
        $sheet = $variant->request->sheet;

        // Проверка прав
        if ($sheet->user_id !== $request->user()->id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Вы можете редактировать только свои заявки'], 403);
            }
            return redirect()->back()->with('error', 'Вы можете редактировать только свои заявки');
        }

        if (!$sheet->isDraft()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Нельзя редактировать варианты в подтвержденной ведомости'], 403);
            }
            return redirect()->back()->with('error', 'Нельзя редактировать варианты в подтвержденной ведомости');
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:scoring_categories,id',
        ]);

        DB::beginTransaction();

        try {
            // Обновляем название варианта
            $variant->update([
                'name' => $validated['name'] ?? $variant->name
            ]);

            // Удаляем старые записи
            $variant->entries()->delete();

            // Создаём новые записи для выбранных категорий
            foreach ($validated['category_ids'] as $categoryId) {
                $category = ScoringCategory::with('parent')->findOrFail($categoryId);
                $parent = $category->parent;

                // Рассчитываем баллы
                $basePoints = $parent ? (float)$parent->base_points : 0;
                $additionalPoints = (float)$category->points;
                $points = $basePoints + $additionalPoints;

                $variant->entries()->create([
                    'sheet_id' => $sheet->id,
                    'request_id' => $variant->request_id,
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

            // Пересчитываем общую сумму ведомости
            $sheet->recalculateTotal();

            DB::commit();

            // ВАЖНО: Возвращаем редирект вместо JSON для Inertia
            return redirect()->back()->with('success', 'Вариант успешно обновлён');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Ошибка при обновлении варианта: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Ошибка при обновлении варианта: ' . $e->getMessage());
        }
    }

    /**
     * Удаление варианта
     */
    public function destroy(ScoringVariant $variant, Request $request)
    {
        $sheet = $variant->request->sheet;

        // Проверка прав
        if ($sheet->user_id !== $request->user()->id) {
            return redirect()->back()->with('error', 'Вы можете удалять только свои варианты');
        }

        if (!$sheet->isDraft()) {
            return redirect()->back()->with('error', 'Нельзя удалять варианты из подтвержденной ведомости');
        }

        DB::transaction(function () use ($variant, $sheet) {
            $variant->delete();
            $sheet->recalculateTotal();
        });

        return redirect()->back()->with('success', 'Вариант удалён');
    }
}
