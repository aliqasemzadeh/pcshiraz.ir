@props([
    'tags' => [],
])

@if (count($tags) > 0)
    <x-shop.carousel :label="__('app.popular_tags')">
        @foreach ($tags as $tag)
            <li
                class="w-1/3 shrink-0 snap-start sm:w-1/4 md:w-1/5 lg:w-1/6"
                role="option"
            >
                <x-shop.item-tag
                    :name="$tag['name']"
                    :slug="$tag['slug']"
                    x-bind="focusableWhenVisible"
                />
            </li>
        @endforeach
    </x-shop.carousel>
@endif
