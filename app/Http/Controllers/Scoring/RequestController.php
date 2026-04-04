<?php

namespace App\Http\Controllers\Scoring;

use App\Http\Controllers\Controller;
use App\Models\ScoringSheet;
use App\Models\ScoringRequest;
use App\Models\ScoringVariant;
use App\Models\ScoringEntry;
use App\Models\ScoringCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestController extends Controller
{
    /**
     * Создание новой заявки с вариантами
     */
    public function store(ScoringSheet $sheet, Request $request)
    {
        // Проверка доступа
        if ($sheet->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Вы можете добавлять записи только в свою ведомость'], 403);
        }

        if (!$sheet->isDraft()) {
            return response()->json(['error' => 'Нельзя добавлять записи в подтвержденную ведомость'], 403);
        }

        $data = $request->all();

        if (empty($data['variants'])) {
            return response()->json(['error' => 'Необходимо добавить хотя бы один вариант'], 400);
        }

        try {
            DB::beginTransaction();

            // Создаем заявку
            $scoringRequest = ScoringRequest::create([
                'sheet_id' => $sheet->id,
                'request_number' => $data['request_number'] ?? null,
                'counterparty' => $data['counterparty'] ?? null,
                'manager_name' => $data['manager_name'] ?? null,
            ]);

            // Создаем варианты и записи
            foreach ($data['variants'] as $index => $variantData) {
                if (empty($variantData['category_ids'])) {
                    continue;
                }

                $variant = ScoringVariant::create([
                    'request_id' => $scoringRequest->id,
                    'name' => $variantData['name'] ?? "Вариант " . ($index + 1),
                    'sort_order' => $index,
                ]);

                foreach ($variantData['category_ids'] as $categoryId) {
                    $category = ScoringCategory::with('parent')->find($categoryId);
                    if (!$category) continue;

                    $parent = $category->parent;
                    $basePoints = $parent ? (float)$parent->base_points : 0;
                    $additionalPoints = (float)$category->points;
                    $points = $basePoints + $additionalPoints;

                    ScoringEntry::create([
                        'sheet_id' => $sheet->id,
                        'request_id' => $scoringRequest->id,
                        'variant_id' => $variant->id,
                        'category_id' => $category->id,
                        'quantity' => 1,
                        'points' => $points,
                        'metadata' => [
                            'parent_name' => $parent ? $parent->name : null,
                            'category_name' => $category->name,
                            'base_points' => $basePoints,
                            'additional_points' => $additionalPoints,
                        ]
                    ]);
                }
            }

            // Пересчитываем общую сумму ведомости
            $sheet->recalculateTotal();

            DB::commit();

            // Загружаем все отношения для ответа
            $scoringRequest->load(['variants.entries.category', 'variants.entries.category.parent']);

            return response()->json([
                'success' => true,
                'message' => 'Заявка успешно добавлена',
                'request' => $scoringRequest
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store error: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка при сохранении заявки: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Обновление заявки
     */
    public function update(Request $request, $id)
    {
        Log::info('=== UPDATE REQUEST ===');
        Log::info('Request ID: ' . $id);

        // Находим заявку с загрузкой sheet
        $scoringRequest = ScoringRequest::with('sheet')->find($id);

        if (!$scoringRequest) {
            Log::error('Request not found: ' . $id);
            return response()->json(['error' => 'Заявка не найдена'], 404);
        }

        $sheet = $scoringRequest->sheet;

        if (!$sheet) {
            Log::error('Sheet not found for request: ' . $id);
            return response()->json(['error' => 'Ведомость не найдена'], 404);
        }

        Log::info('Sheet found:', ['sheet_id' => $sheet->id, 'sheet_user_id' => $sheet->user_id]);

        // Проверка прав
        if ($sheet->user_id !== $request->user()->id) {
            Log::error('Access denied. Sheet user_id: ' . $sheet->user_id . ', Current user_id: ' . $request->user()->id);
            return response()->json(['error' => 'Вы можете редактировать только свои заявки'], 403);
        }

        if (!$sheet->isDraft()) {
            Log::error('Sheet is not draft. Status: ' . $sheet->status);
            return response()->json(['error' => 'Нельзя редактировать заявки в подтвержденной ведомости'], 403);
        }

        $validated = $request->validate([
            'request_number' => 'nullable|string|max:100',
            'counterparty' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'variants' => 'required|array',
            'variants.*.id' => 'nullable|exists:scoring_variants,id',
            'variants.*.name' => 'nullable|string|max:255',
            'variants.*.category_ids' => 'required|array',
            'variants.*.category_ids.*' => 'exists:scoring_categories,id',
        ]);

        Log::info('Validation passed');

        DB::beginTransaction();

        try {
            // 1. Обновляем основную информацию
            $scoringRequest->update([
                'request_number' => $validated['request_number'],
                'counterparty' => $validated['counterparty'],
                'manager_name' => $validated['manager_name'],
            ]);

            Log::info('Request basic info updated');

            // 2. Получаем текущие ID вариантов
            $existingVariantIds = $scoringRequest->variants()->pluck('id')->toArray();
            $keepVariantIds = [];

            // 3. Обрабатываем каждый вариант из запроса
            foreach ($validated['variants'] as $index => $variantData) {
                $variantId = $variantData['id'] ?? null;

                if ($variantId && in_array($variantId, $existingVariantIds)) {
                    // Обновляем существующий вариант
                    $variant = ScoringVariant::find($variantId);
                    if ($variant) {
                        $variant->update([
                            'name' => $variantData['name'] ?? "Вариант " . ($index + 1),
                            'sort_order' => $index,
                        ]);
                        $keepVariantIds[] = $variantId;

                        // Удаляем старые записи варианта
                        $variant->entries()->delete();
                        Log::info('Variant updated and entries deleted', ['variant_id' => $variantId]);
                    }
                } else {
                    // Создаём новый вариант
                    $variant = ScoringVariant::create([
                        'request_id' => $scoringRequest->id,
                        'name' => $variantData['name'] ?? "Вариант " . ($index + 1),
                        'sort_order' => $index,
                    ]);
                    $keepVariantIds[] = $variant->id;
                    Log::info('New variant created', ['variant_id' => $variant->id]);
                }

                // Создаём новые записи для варианта
                foreach ($variantData['category_ids'] as $categoryId) {
                    $category = ScoringCategory::with('parent')->find($categoryId);
                    if (!$category) {
                        Log::warning('Category not found', ['category_id' => $categoryId]);
                        continue;
                    }

                    $parent = $category->parent;
                    $basePoints = $parent ? (float)$parent->base_points : 0;
                    $additionalPoints = (float)$category->points;
                    $points = $basePoints + $additionalPoints;

                    $variant->entries()->create([
                        'sheet_id' => $sheet->id,
                        'request_id' => $scoringRequest->id,
                        'variant_id' => $variant->id,
                        'category_id' => $category->id,
                        'quantity' => 1,
                        'points' => $points,
                        'metadata' => [
                            'parent_name' => $parent ? $parent->name : null,
                            'category_name' => $category->name,
                            'base_points' => $basePoints,
                            'additional_points' => $additionalPoints,
                        ]
                    ]);
                    Log::info('Entry created', ['category_id' => $categoryId, 'points' => $points]);
                }
            }

            // 4. Удаляем варианты, которых нет в запросе
            $variantsToDelete = array_diff($existingVariantIds, $keepVariantIds);
            if (!empty($variantsToDelete)) {
                ScoringVariant::whereIn('id', $variantsToDelete)->delete();
                Log::info('Deleted variants', ['variants' => $variantsToDelete]);
            }

            // 5. Пересчитываем общую сумму
            $sheet->recalculateTotal();

            DB::commit();

            Log::info('Update successful, new total: ' . $sheet->total_points);

            // Загружаем обновлённые данные для ответа
            $scoringRequest->load(['variants.entries.category', 'variants.entries.category.parent']);

            return response()->json([
                'success' => true,
                'message' => 'Заявка успешно обновлена',
                'request' => $scoringRequest,
                'sheet' => $sheet
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Ошибка при обновлении заявки: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Удаление заявки
     */
    public function destroy(ScoringRequest $scoringRequest, Request $request)
    {
        $scoringRequest->load('sheet');
        $sheet = $scoringRequest->sheet;

        if (!$sheet) {
            return redirect()->back()->with('error', 'Ведомость не найдена');
        }

        if ($sheet->user_id !== $request->user()->id) {
            return redirect()->back()->with('error', 'Вы можете удалять только свои заявки');
        }

        if (!$sheet->isDraft()) {
            return redirect()->back()->with('error', 'Нельзя удалять заявки из подтвержденной ведомости');
        }

        DB::transaction(function () use ($scoringRequest, $sheet) {
            $scoringRequest->delete();
            $sheet->recalculateTotal();
        });

        return redirect()->back()->with('success', 'Заявка удалена');
    }
}
