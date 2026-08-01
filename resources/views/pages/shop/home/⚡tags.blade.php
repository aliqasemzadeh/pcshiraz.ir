<?php

use App\Models\Item;
use App\Services\Shop\CatalogCache;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Spatie\Tags\Tag;

new #[Lazy] class extends Component
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
};
?>

@placeholder
    <div class="h-28 animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800"></div>
@endplaceholder

<div>
    <x-shop.tag-carousel :tags="$this->tags" />
</div>
