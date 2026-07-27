<?php

namespace App\Services\Shop;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Item;
use Illuminate\Support\Facades\Cache;

class CategoryMenuService
{
    public function cacheKey(Domain $domain): string
    {
        return "shop.category_menu.{$domain->id}";
    }

    /**
     * @return list<array{id: int, title: string, slug: string, brands: list<array{id: int, title: string, slug: string}>}>
     */
    public function for(Domain $domain): array
    {
        return Cache::remember($this->cacheKey($domain), 3600, function () use ($domain) {
            return $this->build($domain);
        });
    }

    public function forget(Domain $domain): void
    {
        Cache::forget($this->cacheKey($domain));
    }

    /**
     * @return list<array{id: int, title: string, slug: string, brands: list<array{id: int, title: string, slug: string}>}>
     */
    protected function build(Domain $domain): array
    {
        $categories = Category::query()
            ->where('domain_id', $domain->id)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        if ($categories->isEmpty()) {
            return [];
        }

        $brands = Brand::query()
            ->where('domain_id', $domain->id)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug'])
            ->keyBy('id');

        $pairs = Item::query()
            ->where('domain_id', $domain->id)
            ->select('category_id', 'brand_id')
            ->distinct()
            ->get()
            ->groupBy('category_id');

        return $categories->map(function (Category $category) use ($pairs, $brands) {
            $brandIds = ($pairs->get($category->id) ?? collect())
                ->pluck('brand_id')
                ->unique()
                ->filter(fn ($id) => $brands->has($id))
                ->values();

            $categoryBrands = $brandIds
                ->map(fn ($id) => $brands->get($id))
                ->filter()
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['title', 'asc'],
                ])
                ->values()
                ->map(fn (Brand $brand) => [
                    'id' => $brand->id,
                    'title' => $brand->title,
                    'slug' => $brand->slug,
                ])
                ->all();

            return [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => $category->slug,
                'brands' => $categoryBrands,
            ];
        })->all();
    }
}
