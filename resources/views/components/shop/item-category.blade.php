@props([
    'category',
])

@php
    /** @var \App\Models\Category $category */
    $media = $category->getFirstMedia('logo_image');
    $imageUrl = null;

    if ($media !== null) {
        if ($media->mime_type === 'image/svg+xml') {
            $url = $media->getUrl();
        } else {
            $url = $media->getUrl('thumb') ?: $media->getUrl();
        }

        $imageUrl = $url !== '' ? $url : null;
    }

    $fallbackLetter = mb_substr((string) $category->title, 0, 1);
@endphp

<a
    href="{{ route('shop.category', $category) }}"
    wire:navigate
    {{ $attributes->class('group flex flex-col items-center text-center') }}
>
    <div class="mx-auto mb-3 flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-white text-gray-900 transition group-hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white group-hover:dark:bg-gray-700">
        @if ($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $category->title }}"
                class="h-10 w-10 object-contain"
                loading="lazy"
            />
        @else
            <span class="text-lg font-semibold text-gray-400 dark:text-gray-500">{{ $fallbackLetter }}</span>
        @endif
    </div>
    <span class="line-clamp-2 text-sm font-semibold text-gray-900 group-hover:underline dark:text-white">{{ $category->title }}</span>
</a>
