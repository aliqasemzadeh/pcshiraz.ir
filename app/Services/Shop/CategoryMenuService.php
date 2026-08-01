<?php

namespace App\Services\Shop;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Cache;

class CategoryMenuService
{
    public function cacheKey(): string
    {
        return CatalogCache::MENU;
    }

    /**
     * @return list<array{id: int, title: string, slug: string, image: ?string, brands: list<array{id: int, title: string, slug: string, image: ?string}>}>
     */
    public function get(): array
    {
        return Cache::remember($this->cacheKey(), CatalogCache::TTL, function () {
            return $this->build();
        });
    }

    public function forget(): void
    {
        CatalogCache::forgetAll();
    }

    /**
     * @return list<array{id: int, title: string, slug: string, image: ?string, brands: list<array{id: int, title: string, slug: string, image: ?string}>}>
     */
    protected function build(): array
    {
        $categories = Category::query()
            ->with('media')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        if ($categories->isEmpty()) {
            return [];
        }

        $brands = Brand::query()
            ->with('media')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'sort_order'])
            ->keyBy('id');

        $pairs = Item::query()
            ->active()
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
        $media = $model->getFirstMedia('logo_image');

        if ($media === null) {
            return null;
        }

        if ($media->mime_type === 'image/svg+xml') {
            $url = $media->getUrl();

            return $url !== '' ? $url : null;
        }

        $url = $media->getUrl('thumb');

        if ($url === '') {
            $url = $media->getUrl();
        }

        return $url !== '' ? $url : null;
    }
}
