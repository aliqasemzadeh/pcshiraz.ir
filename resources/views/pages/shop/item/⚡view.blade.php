<?php

use App\Models\Item;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Item $item;

    public function mount(Item $item): void
    {
        if (! $item->is_active) {
            abort(404);
        }

        $this->item = $item->load(['brand', 'category', 'media', 'tags', 'activeCashPrice', 'groupVariants' => function ($q) {
            $q->active()->with(['media', 'activeCashPrice']);
        }]);

        $item->increment('views_count');
    }
};
?>

@php
    $cash = $item->activeCashPrice;
    $hasDiscount = $cash && (float) $cash->sale_price < (float) $cash->price;
    $media = $item->getFirstMedia('product_image');
    $imageUrl = $media ? ($media->getUrl('optimized') ?: $media->getUrl()) : null;
@endphp

<div class="space-y-8">
    <nav class="text-sm text-gray-500">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-brand">{{ __('general.home') }}</a>
        <span class="mx-1">/</span>
        <a href="{{ route('shop.category', $item->category) }}" wire:navigate class="hover:text-brand">{{ $item->category?->title }}</a>
        <span class="mx-1">/</span>
        <span class="text-gray-800 dark:text-gray-200">{{ $item->title }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $item->title }}" class="aspect-square w-full object-contain p-6" />
            @else
                <div class="flex aspect-square items-center justify-center text-gray-400">—</div>
            @endif
        </div>

        <div class="space-y-4">
            @if ($item->brand)
                <a href="{{ route('shop.category.brand', [$item->category, $item->brand]) }}" wire:navigate class="text-sm font-medium text-brand hover:underline">
                    {{ $item->brand->title }}
                </a>
            @endif

            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $item->title }}</h1>

            @if ($cash)
                <div class="flex flex-wrap items-baseline gap-3">
                    <span class="text-3xl font-bold text-brand">{{ number_format((float) $cash->sale_price) }}</span>
                    @if ($hasDiscount)
                        <span class="text-lg text-gray-400 line-through">{{ number_format((float) $cash->price) }}</span>
                    @endif
                </div>
            @endif

            @if (! $item->is_purchasable)
                <p class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                    {{ __('app.not_purchasable') }}
                </p>
            @endif

            @if ($item->color_name)
                <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <span>{{ __('general.color') }}:</span>
                    @if ($item->color_code)
                        <span class="inline-block h-4 w-4 rounded-full border" style="background-color: {{ $item->color_code }}"></span>
                    @endif
                    <span>{{ $item->color_name }}</span>
                </div>
            @endif

            @if ($item->description)
                <div class="prose prose-sm max-w-none text-gray-600 dark:text-gray-300">
                    {!! nl2br(e($item->description)) !!}
                </div>
            @endif

            @if ($item->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($item->tags as $tag)
                        <a
                            href="{{ route('shop.tag', (string) $tag->slug) }}"
                            wire:navigate
                            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-brand/10 hover:text-brand dark:bg-gray-700 dark:text-gray-200"
                        >
                            {{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if ($item->groupVariants->where('id', '!=', $item->id)->isNotEmpty())
        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('general.group') }}</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($item->groupVariants->where('id', '!=', $item->id) as $variant)
                    <x-shop.item-card :item="$variant" />
                @endforeach
            </div>
        </section>
    @endif
</div>
