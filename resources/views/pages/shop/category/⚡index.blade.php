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

    public int $perPage = 30;

    #[Url]
    public string $sort = 'price_asc';

    #[Url]
    public string $availability = 'in_stock';

    /** @var list<int> */
    #[Url]
    public array $brands = [];

    /** @var list<string> */
    #[Url]
    public array $colors = [];

    public function mount(Category $category, string $slug): void
    {
        if ($slug !== $category->slug) {
            throw new HttpResponseException(
                redirect()->route('shop.category', $category->shopRoute(), 301)
            );
        }

        $this->category = $category;
        $this->perPage = (int) config('main.per_page', 30);

        defer(fn () => DB::table('categories')->where('id', $category->id)->increment('views_count'));
    }

    public function updatedSort(): void
    {
        $this->resetListing();
    }

    public function updatedAvailability(): void
    {
        $this->resetListing();
    }

    public function updatedBrands(): void
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
            ->when($this->brands !== [], fn (Builder $q) => $q->whereIn('items.brand_id', $this->brands))
            ->when($this->colors !== [], fn (Builder $q) => $q->whereIn('items.color_name', $this->colors));

        if ($this->sort === 'price_desc') {
            $query->orderByDesc('cash_prices.sale_price');
        } else {
            $query->orderBy('cash_prices.sale_price');
        }

        return $query;
    }

    #[Computed]
    public function filterOptions(): array
    {
        return Cache::remember(CatalogCache::categoryFilters($this->category->id), CatalogCache::TTL, function () {
            $base = Item::query()
                ->active()
                ->where('category_id', $this->category->id);

            $brands = Brand::query()
                ->whereIn('id', (clone $base)->select('brand_id')->distinct())
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(['id', 'title', 'slug'])
                ->map(fn (Brand $brand) => [
                    'id' => $brand->id,
                    'title' => $brand->title,
                    'slug' => $brand->slug,
                ])
                ->values()
                ->all();

            $colors = (clone $base)
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

            return [
                'brands' => $brands,
                'colors' => $colors,
            ];
        });
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $category->title }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('general.categories') }}</p>
        </div>

        <x-shop.catalog-filters
            :id="'category-'.$category->id"
            :sort="$sort"
            :availability="$availability"
            :brands="$this->filterOptions['brands']"
            :colors="$this->filterOptions['colors']"
            :selected-brands="$brands"
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
                <x-shop.item-card :item="$item" wire:key="item-{{ $item->id }}" />
            @endforeach
        </div>

        @if ($this->hasMore)
            <div
                wire:intersect="loadMore"
                class="flex items-center justify-center py-6 text-sm text-gray-500"
                wire:loading.class="opacity-100"
            >
                <span wire:loading wire:target="loadMore">{{ __('app.loading_more') }}</span>
            </div>
        @endif
    @endif
</div>
