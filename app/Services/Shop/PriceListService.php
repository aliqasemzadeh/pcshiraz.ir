<?php

namespace App\Services\Shop;

use App\Enums\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemPrice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Morilog\Jalali\Jalalian;

class PriceListService
{
    public function categories(): Collection
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }

    /**
     * @return list<array{id: int|string, title: string}>
     */
    public function brandOptions(?int $categoryId): array
    {
        if ($categoryId === null) {
            return [];
        }

        return Brand::query()
            ->whereIn('id', Item::query()
                ->active()
                ->purchasable()
                ->where('category_id', $categoryId)
                ->select('brand_id')
                ->distinct())
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Brand $brand) => [
                'id' => $brand->id,
                'title' => $brand->title,
            ])
            ->values()
            ->all();
    }

    public function query(?int $categoryId, ?int $brandId = null, ?string $search = null): Builder
    {
        return Item::query()
            ->active()
            ->purchasable()
            ->when($categoryId !== null, fn (Builder $q) => $q->where('items.category_id', $categoryId))
            ->when($brandId !== null, fn (Builder $q) => $q->where('items.brand_id', $brandId))
            ->when($search !== null && trim($search) !== '', function (Builder $q) use ($search) {
                $term = '%'.trim($search).'%';
                $q->where(function (Builder $inner) use ($term) {
                    $inner->where('items.title', 'like', $term)
                        ->orWhere('items.color_name', 'like', $term);
                });
            })
            ->leftJoin('brands', 'brands.id', '=', 'items.brand_id')
            ->leftJoin('item_prices as cash_prices', function ($join) {
                $join->on('cash_prices.item_id', '=', 'items.id')
                    ->where('cash_prices.price_type', PriceTypeEnum::Cash->value)
                    ->where('cash_prices.is_active', true);
            })
            ->with(['brand', 'activeCashPrice'])
            ->select('items.*')
            ->orderBy('brands.sort_order')
            ->orderBy('brands.title')
            ->orderBy('items.group_id')
            ->orderBy('items.color_name')
            ->orderBy('items.title');
    }

    public function paginate(?int $categoryId, ?int $brandId = null, ?string $search = null, ?int $perPage = null): LengthAwarePaginator
    {
        return $this->query($categoryId, $brandId, $search)
            ->paginate($perPage ?? config('main.per_page', 30));
    }

    public function all(?int $categoryId, ?int $brandId = null, ?string $search = null): Collection
    {
        return $this->query($categoryId, $brandId, $search)->get();
    }

    /**
     * @return list<array{label: string, value: float}>
     */
    public function priceHistory(int $itemId): array
    {
        return ItemPrice::query()
            ->where('item_id', $itemId)
            ->where('price_type', PriceTypeEnum::Cash->value)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['sale_price', 'created_at'])
            ->map(fn (ItemPrice $price) => [
                'label' => $price->created_at
                    ? Jalalian::fromDateTime($price->created_at)->format('Y/m/d')
                    : '—',
                'value' => (float) $price->sale_price,
            ])
            ->values()
            ->all();
    }
}
