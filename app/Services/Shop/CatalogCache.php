<?php

namespace App\Services\Shop;

use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    public const MENU = 'shop.category_menu';

    public const HOME_TAGS = 'shop.home.tags';

    public const HOME_CATEGORIES = 'shop.home.categories.v2';

    public const TTL = 3600;

    public static function forgetAll(): void
    {
        Cache::forget(self::MENU);
        Cache::forget(self::HOME_TAGS);
        Cache::forget(self::HOME_CATEGORIES);

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
