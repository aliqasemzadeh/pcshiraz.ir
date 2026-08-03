<?php

use App\Services\Shop\CatalogCache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    /**
     * @return list<int>
     */
    #[Computed]
    public function homeCategoryIds(): array
    {
        return CatalogCache::homeCategoryIdsWithItems();
    }
};
?>

<div class="space-y-10">
    <livewire:pages::shop.home.categories :key="'home-categories'" />

    @forelse ($this->homeCategoryIds as $categoryId)
        <livewire:pages::shop.home.category-row :category-id="$categoryId" :key="'home-cat-'.$categoryId" />
    @empty
        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600">
            {{ __('app.no_products') }}
        </div>
    @endforelse
</div>
