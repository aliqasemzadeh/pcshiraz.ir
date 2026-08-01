@props([
    'id' => 'catalog',
    'sort' => 'price_asc',
    'brands' => null,
    'colors' => [],
    'selectedBrands' => [],
    'selectedColors' => [],
])

@php
    $uid = 'cf-'.$id;
    $sortId = $uid.'-sort';
    $brandId = $uid.'-brand';
    $colorId = $uid.'-color';
    $placement = __('general.direction') === 'rtl' ? 'bottom-end' : 'bottom-start';
    $sortLabel = $sort === 'price_desc'
        ? __('app.sort_price_desc')
        : __('app.sort_price_asc');
    $brandCount = is_array($selectedBrands) ? count($selectedBrands) : 0;
    $colorCount = is_array($selectedColors) ? count($selectedColors) : 0;
    $hasBrands = is_array($brands) && count($brands) > 0;
    $hasColors = is_array($colors) && count($colors) > 0;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}>
    {{-- Sort --}}
    <div>
        <button
            id="{{ $sortId }}-btn"
            data-dropdown-toggle="{{ $sortId }}"
            data-dropdown-placement="{{ $placement }}"
            class="inline-flex items-center justify-center text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none"
            type="button"
        >
            {{ __('app.sort_by') }}: {{ $sortLabel }}
            <x-lucide-chevron-down class="w-4 h-4 ms-1.5 -me-0.5" />
        </button>
        <div id="{{ $sortId }}" class="z-10 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-44">
            <ul class="p-2 text-sm text-body font-medium" aria-labelledby="{{ $sortId }}-btn">
                <li>
                    <button
                        type="button"
                        wire:click="$set('sort', 'price_asc')"
                        class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded {{ $sort === 'price_asc' ? 'bg-neutral-tertiary-medium text-heading' : '' }}"
                    >
                        {{ __('app.sort_price_asc') }}
                    </button>
                </li>
                <li>
                    <button
                        type="button"
                        wire:click="$set('sort', 'price_desc')"
                        class="inline-flex items-center w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded {{ $sort === 'price_desc' ? 'bg-neutral-tertiary-medium text-heading' : '' }}"
                    >
                        {{ __('app.sort_price_desc') }}
                    </button>
                </li>
            </ul>
        </div>
    </div>

    {{-- Brand --}}
    @if ($hasBrands)
        <div>
            <button
                id="{{ $brandId }}-btn"
                data-dropdown-toggle="{{ $brandId }}"
                data-dropdown-placement="{{ $placement }}"
                class="inline-flex items-center justify-center text-heading bg-neutral-primary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none"
                type="button"
            >
                {{ __('app.filter_brand') }}@if ($brandCount > 0) ({{ $brandCount }})@endif
                <x-lucide-chevron-down class="w-4 h-4 ms-1.5 -me-0.5" />
            </button>
            <div id="{{ $brandId }}" class="z-10 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-56">
                <ul class="max-h-64 overflow-y-auto p-2 text-sm text-body font-medium" aria-labelledby="{{ $brandId }}-btn">
                    @foreach ($brands as $brand)
                        <li>
                            <label class="inline-flex items-center gap-2 w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model.live="brands"
                                    value="{{ $brand['id'] }}"
                                    class="rounded border-default-medium text-brand focus:ring-brand"
                                />
                                <span>{{ $brand['title'] }}</span>
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Color --}}
    @if ($hasColors)
        <div>
            <button
                id="{{ $colorId }}-btn"
                data-dropdown-toggle="{{ $colorId }}"
                data-dropdown-placement="{{ $placement }}"
                class="inline-flex items-center justify-center text-heading bg-neutral-primary-medium box-border border border-default-medium hover:bg-neutral-tertiary-medium focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none"
                type="button"
            >
                {{ __('app.filter_color') }}@if ($colorCount > 0) ({{ $colorCount }})@endif
                <x-lucide-chevron-down class="w-4 h-4 ms-1.5 -me-0.5" />
            </button>
            <div id="{{ $colorId }}" class="z-10 hidden bg-neutral-primary-medium border border-default-medium rounded-base shadow-lg w-56">
                <ul class="max-h-64 overflow-y-auto p-2 text-sm text-body font-medium" aria-labelledby="{{ $colorId }}-btn">
                    @foreach ($colors as $color)
                        <li>
                            <label class="inline-flex items-center gap-2 w-full p-2 hover:bg-neutral-tertiary-medium hover:text-heading rounded cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model.live="colors"
                                    value="{{ $color['name'] }}"
                                    class="rounded border-default-medium text-brand focus:ring-brand"
                                />
                                @if ($color['code'])
                                    <span class="inline-block h-3.5 w-3.5 shrink-0 rounded-full border border-default-medium" style="background-color: {{ $color['code'] }}"></span>
                                @endif
                                <span>{{ $color['name'] }}</span>
                            </label>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>
