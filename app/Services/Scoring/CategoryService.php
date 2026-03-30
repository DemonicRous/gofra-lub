<?php
// app/Services/Scoring/CategoryService.php

namespace App\Services\Scoring;

use App\Models\ScoringCategory;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    protected $cacheTTL = 3600;

    /**
     * Получить все категории с иерархией
     */
    public function getCategoriesTree(string $type = null): array
    {
        $cacheKey = 'scoring_categories_tree_' . ($type ?? 'all');

        return Cache::remember($cacheKey, $this->cacheTTL, function () use ($type) {
            $query = ScoringCategory::with(['children' => function($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
                ->whereNull('parent_id')
                ->where('is_active', true);

            if ($type) {
                $query->where('type', $type);
            }

            $roots = $query->orderBy('sort_order')->get();

            return $roots->map(function ($root) {
                return [
                    'id' => $root->id,
                    'name' => $root->name,
                    'type' => $root->type,
                    'base_points' => (float) $root->base_points,
                    'is_multiselect' => (bool) $root->is_multiselect,
                    'children' => $root->children->map(function ($child) use ($root) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'points' => (float) $child->points,
                            'unit' => $child->unit,
                            'parent_id' => $child->parent_id,
                            'total_points' => (float) ($root->base_points + $child->points),
                        ];
                    }),
                ];
            })->toArray();
        });
    }

    /**
     * Создать новую категорию
     */
    public function createCategory(array $data): ScoringCategory
    {
        $category = ScoringCategory::create($data);
        $this->clearCache();
        return $category;
    }

    /**
     * Обновить категорию
     */
    public function updateCategory(ScoringCategory $category, array $data): ScoringCategory
    {
        $category->update($data);
        $this->clearCache();
        return $category;
    }

    /**
     * Удалить категорию
     */
    public function deleteCategory(ScoringCategory $category): bool
    {
        $result = $category->delete();
        $this->clearCache();
        return $result;
    }

    /**
     * Очистить кэш категорий
     */
    public function clearCache(): void
    {
        Cache::forget('scoring_categories_tree_constructor');
        Cache::forget('scoring_categories_tree_designer');
        Cache::forget('scoring_categories_tree_common');
        Cache::forget('scoring_categories_tree_all');
    }

    /**
     * Получить категории для конструктора
     */
    public function getConstructorCategories(): array
    {
        return $this->getCategoriesTree('constructor');
    }

    /**
     * Получить категории для дизайнера
     */
    public function getDesignerCategories(): array
    {
        return $this->getCategoriesTree('designer');
    }
}
