<?php

use App\Models\Category;
use App\Services\Shop\CatalogCache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

new #[Lazy] class extends Component
{
    /**
     * @return \Illuminate\Support\Collection<int, Category>
     */
    #[Computed]
    public function categories()
    {
        $ids = collect(CatalogCache::homeCategoriesPayload())->pluck('id')->all();

        if ($ids === []) {
            return collect();
        }

        $categories = Category::query()
            ->with(['media'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn (int $id) => $categories->get($id))
            ->filter()
            ->values();
    }
};
?>

@placeholder
    <div class="h-28 animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800" wire:key="home-categories-placeholder"></div>
@endplaceholder

<div>
    <x-shop.category-carousel :categories="$this->categories" />
</div>
