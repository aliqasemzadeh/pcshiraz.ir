@props([
    'item',
    'showBrand' => true,
])

@php
    /** @var \App\Models\Item $item */
    $media = $item->getFirstMedia('product_image');
    $imageUrl = $media ? ($media->getUrl('thumb') ?: $media->getUrl()) : null;
    $cash = $item->relationLoaded('activeCashPrice') ? $item->activeCashPrice : $item->activeCashPrice()->first();
    $hasDiscount = $cash && (float) $cash->sale_price < (float) $cash->price;
@endphp

<a
    href="{{ route('shop.item', $item) }}"
    wire:navigate
    class="group flex h-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white transition hover:border-brand/40 hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
>
    <div class="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-900">
        @if ($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $item->title }}"
                class="h-full w-full object-contain p-3 transition duration-300 group-hover:scale-105"
                loading="lazy"
            />
        @else
            <div class="flex h-full items-center justify-center text-sm text-gray-400">—</div>
        @endif

        @if (! $item->is_purchasable)
            <span class="absolute start-2 top-2 rounded bg-rose-600 px-2 py-0.5 text-xs font-medium text-white">
                {{ __('app.not_purchasable') }}
            </span>
        @elseif ($hasDiscount)
            <span class="absolute start-2 top-2 rounded bg-emerald-600 px-2 py-0.5 text-xs font-medium text-white">
                {{ __('app.discount') }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-1.5 p-3">
        @if ($showBrand && $item->brand)
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item->brand->title }}</span>
        @endif

        <h3 class="line-clamp-2 text-sm font-semibold leading-6 text-gray-900 dark:text-white">
            {{ $item->title }}
        </h3>

        <div class="mt-auto pt-2">
            @if ($item->is_contact_price)
                <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">
                    {{ __('general.contact_price') }}
                </span>
            @elseif ($cash)
                <div class="flex flex-wrap items-baseline gap-2">
                    <span class="text-base font-bold text-brand">
                        {{ number_format((float) $cash->sale_price) }}
                    </span>
                    @if ($hasDiscount)
                        <span class="text-sm text-gray-400 line-through">
                            {{ number_format((float) $cash->price) }}
                        </span>
                    @endif
                </div>
            @else
                <span class="text-sm text-gray-400">—</span>
            @endif
        </div>
    </div>
</a>
