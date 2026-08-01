<?php

use App\Models\Category;
use App\Models\Item;
use App\Services\Shop\CatalogCache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

new #[Lazy] class extends Component
{
    public int $categoryId;

    #[Computed]
    public function category(): ?Category
    {
        $payload = collect(CatalogCache::homeCategoriesPayload())
            ->firstWhere('id', $this->categoryId);

        if ($payload === null) {
            return null;
        }

        $category = Category::query()->find($this->categoryId);

        if ($category === null) {
            return null;
        }

        /** @var list<int> $itemIds */
        $itemIds = $payload['item_ids'] ?? [];

        $items = $itemIds === []
            ? collect()
            : Item::query()
                ->active()
                ->with(['brand', 'media', 'activeCashPrice'])
                ->whereIn('id', $itemIds)
                ->get()
                ->keyBy('id');

        $category->setRelation(
            'homeItems',
            collect($itemIds)
                ->map(fn (int $id) => $items->get($id))
                ->filter()
                ->values()
        );

        return $category;
    }
};
?>

@placeholder
    <div class="space-y-4" wire:key="home-category-{{ $categoryId }}-placeholder">
        <div class="h-7 w-48 animate-pulse rounded bg-gray-100 dark:bg-gray-800"></div>
        <div class="h-64 animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800"></div>
    </div>
@endplaceholder

<div>
    @if ($this->category && $this->category->homeItems->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $this->category->title }}</h2>
                <a
                    href="{{ route('shop.category', $this->category) }}"
                    wire:navigate
                    class="text-sm font-medium text-brand hover:underline"
                >
                    {{ __('general.view_all_in_category') }}
                </a>
            </div>

            <x-shop.carousel :label="$this->category->title">
                @foreach ($this->category->homeItems as $item)
                    <li
                        class="w-1/2 shrink-0 snap-start sm:w-1/3 md:w-1/4 lg:w-1/6"
                        role="option"
                    >
                        <x-shop.item-card :item="$item" />
                    </li>
                @endforeach
            </x-shop.carousel>
        </section>
    @endif
</div>
