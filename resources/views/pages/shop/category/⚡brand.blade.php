<?php

use App\Enums\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Services\Shop\CatalogCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Category $category;

    public Brand $brand;

    public int $perPage = 30;

    #[Url]
    public string $sort = 'price_asc';

    #[Url]
    public string $availability = 'in_stock';

    /** @var list<string> */
    #[Url]
    public array $colors = [];

    public function mount(Category $category, string $slug, Brand $brand, string $brandSlug): void
    {
        if ($slug !== $category->slug || $brandSlug !== $brand->slug) {
            throw new HttpResponseException(
                redirect()->route('shop.category.brand', $category->shopBrandRoute($brand), 301)
            );
        }

        $this->category = $category;
        $this->brand = $brand;
        $this->perPage = (int) config('main.per_page', 30);

        $hasItems = Item::query()
            ->active()
            ->where('category_id', $category->id)
            ->where('brand_id', $brand->id)
            ->exists();

        if (! $hasItems) {
            abort(404);
        }

        defer(function () use ($category, $brand): void {
            DB::table('categories')->where('id', $category->id)->increment('views_count');
            DB::table('brands')->where('id', $brand->id)->increment('views_count');
        });
    }

    public function updatedSort(): void
    {
        $this->resetListing();
    }

    public function updatedAvailability(): void
    {
        $this->resetListing();
    }

    public function updatedColors(): void
    {
        $this->resetListing();
    }

    public function loadMore(): void
    {
        if (! $this->hasMore) {
            return;
        }

        $this->perPage += (int) config('main.per_page', 30);
        unset($this->fetchedItems, $this->items, $this->hasMore);
    }

    protected function resetListing(): void
    {
        $this->perPage = (int) config('main.per_page', 30);
        unset($this->fetchedItems, $this->items, $this->hasMore);
    }

    protected function itemsQuery(): Builder
    {
        $query = Item::query()
            ->active()
            ->where('items.category_id', $this->category->id)
            ->where('items.brand_id', $this->brand->id)
            ->with(['brand', 'media', 'activeCashPrice', 'activeInstallmentPrice'])
            ->leftJoin('item_prices as cash_prices', function ($join) {
                $join->on('cash_prices.item_id', '=', 'items.id')
                    ->where('cash_prices.price_type', PriceTypeEnum::Cash->value)
                    ->where('cash_prices.is_active', true);
            })
            ->select('items.*')
            ->when($this->availability === 'in_stock', fn (Builder $q) => $q->purchasable())
            ->when(
                $this->availability === 'out_of_stock',
                fn (Builder $q) => $q->where('items.is_purchasable', false),
            )
            ->when($this->colors !== [], fn (Builder $q) => $q->whereIn('items.color_name', $this->colors));

        if ($this->sort === 'price_desc') {
            $query->orderByDesc('cash_prices.sale_price');
        } else {
            $query->orderBy('cash_prices.sale_price');
        }

        return $query;
    }

    #[Computed]
    public function colorOptions(): array
    {
        return Cache::remember(
            CatalogCache::categoryBrandColors($this->category->id, $this->brand->id),
            CatalogCache::TTL,
            function () {
                return Item::query()
                    ->active()
                    ->where('category_id', $this->category->id)
                    ->where('brand_id', $this->brand->id)
                    ->whereNotNull('color_name')
                    ->where('color_name', '!=', '')
                    ->select('color_name', 'color_code')
                    ->distinct()
                    ->orderBy('color_name')
                    ->get()
                    ->map(fn ($row) => [
                        'name' => $row->color_name,
                        'code' => $row->color_code,
                    ])
                    ->values()
                    ->all();
            },
        );
    }

    #[Computed]
    public function fetchedItems()
    {
        return $this->itemsQuery()->limit($this->perPage + 1)->get();
    }

    #[Computed]
    public function items()
    {
        return $this->fetchedItems->take($this->perPage)->values();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->fetchedItems->count() > $this->perPage;
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $category->title }}
                <span class="text-gray-400">/</span>
                {{ $brand->title }}
            </h1>
            <a href="{{ route('shop.category', $category->shopRoute()) }}" wire:navigate.hover class="mt-1 inline-block text-sm text-brand hover:underline">
                {{ __('general.view_all_in_category') }}
            </a>
        </div>

        <x-shop.catalog-filters
            :id="'category-brand-'.$category->id.'-'.$brand->id"
            :sort="$sort"
            :availability="$availability"
            :colors="$this->colorOptions"
            :selected-colors="$colors"
        />
    </div>

    @if ($this->items->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600">
            {{ __('app.no_products') }}
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($this->items as $item)
                <x-shop.item-card :item="$item" :show-brand="false" wire:key="item-{{ $item->id }}" />
            @endforeach
        </div>

        @if ($this->hasMore)
            <div
                wire:intersect="loadMore"
                class="flex items-center justify-center py-6 text-sm text-gray-500"
            >
                <span wire:loading wire:target="loadMore">{{ __('app.loading_more') }}</span>
            </div>
        @endif
    @endif
</div>
