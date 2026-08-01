@props([
    'categories' => [],
])

@php
    $categories = collect($categories)->filter()->values();
@endphp

@if ($categories->isNotEmpty())
    <x-shop.carousel :title="__('app.popular_categories')" :label="__('app.popular_categories')" :skip="4">
        @foreach ($categories as $category)
            <li
                x-bind="disableNextAndPreviousButtons"
                class="w-24 shrink-0 snap-start sm:w-28"
                role="option"
            >
                <x-shop.item-category
                    :category="$category"
                    x-bind="focusableWhenVisible"
                />
            </li>
        @endforeach
    </x-shop.carousel>
@endif
