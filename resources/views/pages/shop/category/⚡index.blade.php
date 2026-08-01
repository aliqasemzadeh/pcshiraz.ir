<?php

use App\Enums\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Cache;
use App\Services\Shop\CatalogCache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public Category $category;

    #[Url]
    public string $sort = 'price_asc';

    /** @var list<int> */
    #[Url]
    public array $brands = [];

    /** @var list<string> */
    #[Url]
    public array $colors = [];

    public function mount(Category $category): void
    {
        $this->category = $category;
        $category->increment('views_count');
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedBrands(): void
    {
        $this->resetPage();
    }

    public function updatedColors(): void
    {
        $this->resetPage();
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
                ->get(['id', 'title', 'slug']);

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
    public function items()
    {
        $query = Item::query()
            ->active()
            ->where('items.category_id', $this->category->id)
            ->with(['brand', 'media', 'activeCashPrice'])
            ->leftJoin('item_prices as cash_prices', function ($join) {
                $join->on('cash_prices.item_id', '=', 'items.id')
                    ->where('cash_prices.price_type', PriceTypeEnum::Cash->value)
                    ->where('cash_prices.is_active', true);
            })
            ->select('items.*')
            ->when($this->brands !== [], fn ($q) => $q->whereIn('items.brand_id', $this->brands))
            ->when($this->colors !== [], fn ($q) => $q->whereIn('items.color_name', $this->colors));

        if ($this->sort === 'price_desc') {
            $query->orderByDesc('cash_prices.sale_price');
        } else {
            $query->orderBy('cash_prices.sale_price');
        }

        return $query->paginate(config('main.per_page', 24));
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $category->title }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('general.categories') }}</p>
        </div>

        <div class="min-w-48">
            <x-fwb.select
                wire:model.live="sort"
                :label="__('app.cash_price')"
                :options="[
                    'price_asc' => __('app.sort_price_asc'),
                    'price_desc' => __('app.sort_price_desc'),
                ]"
            />
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[240px_1fr]">
        <aside class="space-y-6 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <div>
                <h2 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ __('app.filter_brand') }}</h2>
                <div class="max-h-64 space-y-2 overflow-y-auto">
                    @foreach ($this->filterOptions['brands'] as $brand)
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <input type="checkbox" wire:model.live="brands" value="{{ $brand->id }}" class="rounded border-gray-300 text-brand focus:ring-brand" />
                            <span>{{ $brand->title }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            @if (count($this->filterOptions['colors']) > 0)
                <div>
                    <h2 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ __('app.filter_color') }}</h2>
                    <div class="max-h-48 space-y-2 overflow-y-auto">
                        @foreach ($this->filterOptions['colors'] as $color)
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                <input type="checkbox" wire:model.live="colors" value="{{ $color['name'] }}" class="rounded border-gray-300 text-brand focus:ring-brand" />
                                @if ($color['code'])
                                    <span class="inline-block h-3.5 w-3.5 rounded-full border border-gray-300" style="background-color: {{ $color['code'] }}"></span>
                                @endif
                                <span>{{ $color['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>

        <div>
            @if ($this->items->isEmpty())
                <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600">
                    {{ __('app.no_products') }}
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($this->items as $item)
                        <x-shop.item-card :item="$item" />
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $this->items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
