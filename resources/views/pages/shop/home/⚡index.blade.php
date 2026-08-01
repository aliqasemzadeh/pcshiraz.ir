<?php

use App\Models\Category;
use App\Models\Item;
use App\Services\Shop\CatalogCache;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Tags\Tag;

new #[Layout('layouts.app')] class extends Component
{
    /**
     * @return list<array{name: string, slug: string}>
     */
    #[Computed]
    public function tags(): array
    {
        return Cache::remember(CatalogCache::HOME_TAGS, CatalogCache::TTL, function () {
            $tagIds = \Illuminate\Support\Facades\DB::table('taggables')
                ->join('items', function ($join) {
                    $join->on('items.id', '=', 'taggables.taggable_id')
                        ->where('taggables.taggable_type', (new Item)->getMorphClass())
                        ->where('items.is_active', true)
                        ->whereNull('items.deleted_at');
                })
                ->distinct()
                ->limit(24)
                ->pluck('taggables.tag_id');

            return Tag::query()
                ->whereIn('id', $tagIds)
                ->orderBy('order_column')
                ->orderBy('id')
                ->get()
                ->map(fn (Tag $tag) => [
                    'name' => (string) $tag->name,
                    'slug' => (string) $tag->slug,
                ])
                ->filter(fn (array $tag) => $tag['name'] !== '' && $tag['slug'] !== '')
                ->values()
                ->all();
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, Category>
     */
    #[Computed]
    public function homeCategories()
    {
        return Cache::remember(CatalogCache::HOME_CATEGORIES, CatalogCache::TTL, function () {
            return Category::query()
                ->showOnHome()
                ->with(['media'])
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get()
                ->each(function (Category $category) {
                    $category->setRelation(
                        'homeItems',
                        Item::query()
                            ->active()
                            ->where('category_id', $category->id)
                            ->with(['brand', 'media', 'activeCashPrice'])
                            ->orderByDesc('id')
                            ->limit(12)
                            ->get()
                    );
                });
        });
    }
};
?>

<div class="space-y-10">
    <x-shop.tag-carousel :tags="$this->tags" />

    @forelse ($this->homeCategories as $category)
        <section class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $category->title }}</h2>
                <a
                    href="{{ route('shop.category', $category) }}"
                    wire:navigate
                    class="text-sm font-medium text-brand hover:underline"
                >
                    {{ __('general.view_all_in_category') }}
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($category->homeItems as $item)
                    <x-shop.item-card :item="$item" />
                @endforeach
            </div>
        </section>
    @empty
        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600">
            {{ __('app.no_products') }}
        </div>
    @endforelse
</div>
