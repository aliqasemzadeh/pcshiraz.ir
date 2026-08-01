<?php

use App\Enums\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public Category $category;

    public Brand $brand;

    #[Url]
    public string $sort = 'price_asc';

    /** @var list<string> */
    #[Url]
    public array $colors = [];

    public function mount(Category $category, Brand $brand): void
    {
        $this->category = $category;
        $this->brand = $brand;

        $hasItems = Item::query()
            ->active()
            ->where('category_id', $category->id)
            ->where('brand_id', $brand->id)
            ->exists();

        if (! $hasItems) {
            abort(404);
        }

        $category->increment('views_count');
        $brand->increment('views_count');
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedColors(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function colorOptions(): array
    {
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
    }

    #[Computed]
    public function items()
    {
        $query = Item::query()
            ->active()
            ->where('items.category_id', $this->category->id)
            ->where('items.brand_id', $this->brand->id)
            ->with(['brand', 'media', 'activeCashPrice'])
            ->leftJoin('item_prices as cash_prices', function ($join) {
                $join->on('cash_prices.item_id', '=', 'items.id')
                    ->where('cash_prices.price_type', PriceTypeEnum::Cash->value)
                    ->where('cash_prices.is_active', true);
            })
            ->select('items.*')
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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $category->title }}
                <span class="text-gray-400">/</span>
                {{ $brand->title }}
            </h1>
            <a href="{{ route('shop.category', $category) }}" wire:navigate class="mt-1 inline-block text-sm text-brand hover:underline">
                {{ __('general.view_all_in_category') }}
            </a>
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
            @if (count($this->colorOptions) > 0)
                <div>
                    <h2 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ __('app.filter_color') }}</h2>
                    <div class="max-h-64 space-y-2 overflow-y-auto">
                        @foreach ($this->colorOptions as $color)
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
                        <x-shop.item-card :item="$item" :show-brand="false" />
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $this->items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
