<?php

namespace App\Services\Shop;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    public const MENU = 'shop.category_menu';

    public const HOME_TAGS = 'shop.home.tags';

    public const HOME_CATEGORIES = 'shop.home.categories.v2';

    public const HOME_CATEGORY_IDS = 'shop.home.category_ids.v1';

    public const TTL = 3600;

    /**
     * @return list<array{id: int, item_ids: list<int>}>
     */
    public static function homeCategoriesPayload(): array
    {
        return Cache::remember(self::HOME_CATEGORIES, self::TTL, function () {
            return Category::query()
                ->showOnHome()
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(['id'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'item_ids' => Item::query()
                        ->active()
                        ->where('category_id', $category->id)
                        ->orderByDesc('id')
                        ->limit(12)
                        ->pluck('id')
                        ->all(),
                ])
                ->all();
        });
    }

    /**
     * @return list<int>
     */
    public static function homeCategoryIdsWithItems(): array
    {
        return Cache::remember(self::HOME_CATEGORY_IDS, self::TTL, function () {
            return collect(self::homeCategoriesPayload())
                ->filter(fn (array $row) => ($row['item_ids'] ?? []) !== [])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        });
    }

    public static function forgetAll(): void
    {
        Cache::forget(self::MENU);
        Cache::forget(self::HOME_TAGS);
        Cache::forget(self::HOME_CATEGORIES);
        Cache::forget(self::HOME_CATEGORY_IDS);

        // Legacy keys (domain-scoped menu + Eloquent-serialized homepage payload).
        Cache::forget('shop.category_menu.v2');
        Cache::forget('shop.home.categories');
    }

    public static function categoryFilters(int $categoryId): string
    {
        return "shop.category.{$categoryId}.filters.v2";
    }

    public static function forgetCategory(int $categoryId): void
    {
        Cache::forget(self::categoryFilters($categoryId));
        Cache::forget("shop.category.{$categoryId}.filters");
    }
}
