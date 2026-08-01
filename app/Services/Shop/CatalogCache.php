<?php

namespace App\Services\Shop;

use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    public const MENU = 'shop.category_menu';

    public const HOME_TAGS = 'shop.home.tags';

    public const HOME_CATEGORIES = 'shop.home.categories';

    public const TTL = 3600;

    public static function forgetAll(): void
    {
        Cache::forget(self::MENU);
        Cache::forget(self::HOME_TAGS);
        Cache::forget(self::HOME_CATEGORIES);

        // Legacy keys from domain-scoped menu.
        Cache::forget('shop.category_menu.v2');
    }

    public static function categoryFilters(int $categoryId): string
    {
        return "shop.category.{$categoryId}.filters";
    }

    public static function forgetCategory(int $categoryId): void
    {
        Cache::forget(self::categoryFilters($categoryId));
    }
}
