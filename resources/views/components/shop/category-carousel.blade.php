@props([
    'categories' => [],
])

@php
    $categories = collect($categories)->filter()->values();
@endphp

@if ($categories->isNotEmpty())
    <x-shop.carousel :title="__('app.popular_categories')" :label="__('app.popular_categories')">
        @foreach ($categories as $category)
            <li
                x-bind="disableNextAndPreviousButtons"
                class="w-1/3 shrink-0 snap-start sm:w-1/4 md:w-1/5 lg:w-1/6"
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
