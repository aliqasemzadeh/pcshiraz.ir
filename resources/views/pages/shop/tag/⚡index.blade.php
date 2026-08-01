<?php

use App\Models\Item;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Tags\Tag;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $tagSlug = '';

    public string $tagName = '';

    public function mount(string $tag): void
    {
        $tagModel = Tag::findFromString($tag) ?? Tag::query()->where('slug->fa', $tag)->orWhere('slug->en', $tag)->orWhere('slug', $tag)->first();

        if ($tagModel === null) {
            // Try JSON slug contains
            $tagModel = Tag::query()
                ->get()
                ->first(fn (Tag $t) => (string) $t->slug === $tag || (string) $t->name === $tag);
        }

        if ($tagModel === null) {
            abort(404);
        }

        $this->tagSlug = (string) $tagModel->slug;
        $this->tagName = (string) $tagModel->name;
    }

    #[Computed]
    public function items()
    {
        return Item::query()
            ->active()
            ->withAnyTags([$this->tagName])
            ->with(['brand', 'media', 'activeCashPrice'])
            ->orderByDesc('id')
            ->paginate(config('main.per_page', 24));
    }
};
?>

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $tagName }}</h1>

    @if ($this->items->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600">
            {{ __('app.no_products') }}
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach ($this->items as $item)
                <x-shop.item-card :item="$item" />
            @endforeach
        </div>
        <div class="mt-6">
            {{ $this->items->links() }}
        </div>
    @endif
</div>
