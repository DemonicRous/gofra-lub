<?php
// app/Http/Controllers/Scoring/RequestController.php

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
        // Включаем логирование для отладки
        Log::info('=== REQUEST CONTROLLER START ===');
        Log::info('Sheet ID: ' . $sheet->id);
        Log::info('Current user ID: ' . $request->user()->id);
        Log::info('Sheet user_id: ' . $sheet->user_id);
        Log::info('Sheet status: ' . $sheet->status);

        // Проверка доступа
        if ($sheet->user_id !== $request->user()->id) {
            Log::error('Access denied: user_id mismatch');
            return response()->json(['error' => 'Вы можете добавлять записи только в свою ведомость'], 403);
        }

        if (!$sheet->isDraft()) {
            Log::error('Sheet is not draft, status: ' . $sheet->status);
            return response()->json(['error' => 'Нельзя добавлять записи в подтвержденную ведомость'], 403);
        }

        // Получаем данные из запроса
        $data = $request->all();
        Log::info('Request data: ', $data);

        // Проверяем наличие обязательных полей
        if (empty($data['variants'])) {
            Log::error('No variants provided');
            return response()->json(['error' => 'Необходимо добавить хотя бы один вариант'], 400);
        }

        try {
            DB::beginTransaction();

            // 1. Создаем заявку
            $scoringRequest = ScoringRequest::create([
                'sheet_id' => $sheet->id,
                'request_number' => $data['request_number'] ?? null,
                'counterparty' => $data['counterparty'] ?? null,
                'manager_name' => $data['manager_name'] ?? null,
            ]);

            Log::info('Request created with ID: ' . $scoringRequest->id);

            // 2. Создаем варианты
            foreach ($data['variants'] as $index => $variantData) {
                // Пропускаем если нет выбранных категорий
                if (empty($variantData['category_ids'])) {
                    Log::warning('Variant ' . $index . ' has no category_ids, skipping');
                    continue;
                }

                $variant = ScoringVariant::create([
                    'request_id' => $scoringRequest->id,
                    'name' => $variantData['name'] ?? "Вариант " . ($index + 1),
                    'sort_order' => $index,
                ]);

                Log::info('Variant created with ID: ' . $variant->id . ', name: ' . $variant->name);

                // 3. Создаем записи для каждой категории
                foreach ($variantData['category_ids'] as $categoryId) {
                    $category = ScoringCategory::with('parent')->find($categoryId);
                    if (!$category) {
                        Log::error('Category not found: ' . $categoryId);
                        continue;
                    }

                    $parent = $category->parent;
                    $basePoints = $parent ? (float)$parent->base_points : 0;
                    $additionalPoints = (float)$category->points;
                    $points = $basePoints + $additionalPoints;

                    Log::info('Category: ' . $category->name . ', Points: ' . $points);

                    ScoringEntry::create([
                        'sheet_id' => $sheet->id,
                        'request_id' => $scoringRequest->id,  // request_id, не scoring_request_id
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

            // 4. Обновляем общую сумму в ведомости
            $totalPoints = $scoringRequest->entries()->sum('points');
            $sheet->total_points = $sheet->total_points + $totalPoints;
            $sheet->save();

            Log::info('Sheet total points updated to: ' . $sheet->total_points);

            DB::commit();
            Log::info('Transaction committed successfully');

            // Возвращаем успешный ответ
            return response()->json([
                'success' => true,
                'message' => 'Заявка успешно добавлена',
                'request' => $scoringRequest->load(['variants.entries.category', 'variants.entries.category.parent'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ERROR: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => true,
                'message' => 'Заявка успешно добавлена',
                'request' => $scoringRequest->load([
                    'variants' => function($query) {
                        $query->orderBy('sort_order');
                    },
                    'variants.entries.category',
                    'variants.entries.category.parent'
                ])
            ]);
        }
    }
}
