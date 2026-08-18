@php
    $isMobile = $mobile ?? false;
    $hasQuery = filled(trim($q));
@endphp

<div wire:loading.flex wire:target="q" class="flex-col items-center justify-center gap-2 px-4 py-10 text-navbar-fg">
    <x-lucide-loader-circle class="h-6 w-6 animate-spin text-brand" />
    <p class="text-sm">{{ __('general.search') }}...</p>
</div>

<div wire:loading.remove wire:target="q">
    @if (! $hasQuery)
        <div class="flex flex-col items-center justify-center gap-3 px-4 py-12 text-center {{ $isMobile ? 'min-h-[50vh]' : '' }}">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-brand/10 text-brand">
                <x-lucide-search class="h-7 w-7" />
            </span>
            <p class="text-sm font-medium text-ink md:text-base">{{ __('general.search_waiting') }}</p>
            <p class="max-w-xs text-xs text-navbar-fg">{{ __('general.search_products') }}</p>
        </div>
    @elseif ($this->results->isEmpty())
        <div class="flex flex-col items-center justify-center gap-3 px-4 py-12 text-center {{ $isMobile ? 'min-h-[40vh]' : '' }}">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-canvas text-navbar-fg ring-1 ring-nav-border">
                <x-lucide-search-x class="h-7 w-7" />
            </span>
            <p class="text-sm font-medium text-ink">{{ __('general.search_no_results') }}</p>
        </div>
    @else
        <ul class="divide-y divide-nav-border" role="presentation">
            @foreach ($this->results as $item)
                @php
                    $media = $item->getFirstMedia('product_image');
                    $imageUrl = $media ? ($media->getUrl('thumb') ?: $media->getUrl()) : null;
                    $cash = $item->activeCashPrice;
                    $hasDiscount = $cash && (float) $cash->sale_price < (float) $cash->price;
                @endphp
                <li role="option">
                    <a
                        href="{{ route('shop.item', $item->shopRoute()) }}"
                        wire:navigate
                        @click="closePanel()"
                        class="flex items-center gap-3 px-3 py-2.5 transition hover:bg-brand-softer md:px-4 md:py-3"
                    >
                        <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-canvas ring-1 ring-nav-border md:h-16 md:w-16">
                            @if ($imageUrl)
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $item->title }}"
                                    class="h-full w-full object-contain p-1.5"
                                    loading="lazy"
                                >
                            @else
                                <x-lucide-package class="h-6 w-6 text-navbar-fg" />
                            @endif
                        </span>

                        <span class="min-w-0 flex-1">
                            @if ($item->brand)
                                <span class="mb-0.5 block truncate text-[11px] text-navbar-fg md:text-xs">{{ $item->brand->title }}</span>
                            @endif
                            <span class="line-clamp-2 text-sm font-medium text-ink">{{ $item->title }}</span>
                        </span>

                        <span class="shrink-0 text-end">
                            @if ($item->is_contact_price)
                                <span class="text-xs font-semibold text-amber-600 md:text-sm">{{ __('general.contact_price') }}</span>
                            @elseif ($cash)
                                <span class="block text-sm font-bold text-brand md:text-base">{{ format_price((float) $cash->sale_price) }}</span>
                                @if ($hasDiscount)
                                    <span class="block text-[11px] text-navbar-fg line-through">{{ format_price((float) $cash->price) }}</span>
                                @endif
                            @else
                                <span class="text-xs text-navbar-fg">—</span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
