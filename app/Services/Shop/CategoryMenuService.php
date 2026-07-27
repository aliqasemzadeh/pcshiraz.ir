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
        return "shop.category_menu.v2.{$domain->id}";
    }

    /**
     * @return list<array{id: int, title: string, slug: string, image: ?string, brands: list<array{id: int, title: string, slug: string, image: ?string}>}>
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
        Cache::forget("shop.category_menu.{$domain->id}");
    }

    /**
     * @return list<array{id: int, title: string, slug: string, image: ?string, brands: list<array{id: int, title: string, slug: string, image: ?string}>}>
     */
    protected function build(Domain $domain): array
    {
        $categories = Category::query()
            ->where('domain_id', $domain->id)
            ->with('media')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        if ($categories->isEmpty()) {
            return [];
        }

        $brands = Brand::query()
            ->where('domain_id', $domain->id)
            ->with('media')
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
                    'image' => $this->mediaUrl($brand),
                ])
                ->all();

            return [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => $category->slug,
                'image' => $this->mediaUrl($category),
                'brands' => $categoryBrands,
            ];
        })->all();
    }

    protected function mediaUrl(Category|Brand $model): ?string
    {
        $url = $model->getFirstMediaUrl('logo_image', 'thumb');

        if ($url === '') {
            $url = $model->getFirstMediaUrl('logo_image');
        }

        return $url !== '' ? $url : null;
    }
}
