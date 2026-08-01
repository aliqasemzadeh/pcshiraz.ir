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
        /** @var list<array{id: int, item_ids: list<int>}> $payload */
        $payload = Cache::remember(CatalogCache::HOME_CATEGORIES, CatalogCache::TTL, function () {
            return Category::query()
                ->showOnHome()
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(['id'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'item_ids' => Item::query()
                        ->active()
                        ->where('category_id', $category->id)
                        ->orderByDesc('id')
                        ->limit(12)
                        ->pluck('id')
                        ->all(),
                ])
                ->all();
        });

        if ($payload === []) {
            return collect();
        }

        $categoryIds = array_column($payload, 'id');
        $itemIds = collect($payload)->pluck('item_ids')->flatten()->unique()->values()->all();

        $categories = Category::query()
            ->with(['media'])
            ->whereIn('id', $categoryIds)
            ->get()
            ->keyBy('id');

        $items = $itemIds === []
            ? collect()
            : Item::query()
                ->active()
                ->with(['brand', 'media', 'activeCashPrice'])
                ->whereIn('id', $itemIds)
                ->get()
                ->keyBy('id');

        return collect($payload)
            ->map(function (array $row) use ($categories, $items) {
                $category = $categories->get($row['id']);

                if ($category === null) {
                    return null;
                }

                $category->setRelation(
                    'homeItems',
                    collect($row['item_ids'])
                        ->map(fn (int $id) => $items->get($id))
                        ->filter()
                        ->values()
                );

                return $category;
            })
            ->filter()
            ->values();
    }
};
?>

<div class="space-y-10">
    <x-shop.tag-carousel :tags="$this->tags" />

    <x-shop.category-carousel :categories="$this->homeCategories" />

    @forelse ($this->homeCategories as $category)
        @continue ($category->homeItems->isEmpty())

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

            <x-shop.carousel :label="$category->title">
                @foreach ($category->homeItems as $item)
                    <li
                        x-bind="disableNextAndPreviousButtons"
                        class="w-1/2 shrink-0 snap-start sm:w-1/3 md:w-1/4 lg:w-1/6"
                        role="option"
                    >
                        <x-shop.item-card :item="$item" />
                    </li>
                @endforeach
            </x-shop.carousel>
        </section>
    @empty
        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600">
            {{ __('app.no_products') }}
        </div>
    @endforelse
</div>
