@props([
    'category',
])

@php
    /** @var \App\Models\Category $category */
    $media = $category->getFirstMedia('logo_image');
    $imageUrl = $media ? ($media->getUrl('thumb') ?: $media->getUrl()) : null;
@endphp

<a
    href="{{ route('shop.category', $category) }}"
    wire:navigate
    {{ $attributes->class('flex aspect-square flex-col items-center justify-center gap-2 overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-3 text-center shadow-sm transition hover:border-brand/40 hover:shadow dark:border-gray-700 dark:from-gray-800 dark:to-gray-900') }}
>
    <div class="flex w-full flex-1 items-center justify-center overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-900">
        @if ($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $category->title }}"
                class="h-full w-full object-contain p-2"
                loading="lazy"
            />
        @else
            <span class="text-sm text-gray-400">—</span>
        @endif
    </div>
    <span class="line-clamp-2 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $category->title }}</span>
</a>
