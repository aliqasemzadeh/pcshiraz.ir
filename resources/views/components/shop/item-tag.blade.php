@props([
    'name',
    'slug',
])

<a
    href="{{ route('shop.tag', $slug) }}"
    wire:navigate
    {{ $attributes->class('flex aspect-square flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-3 text-center shadow-sm transition hover:border-brand/40 hover:shadow dark:border-gray-700 dark:from-gray-800 dark:to-gray-900') }}
>
    <span class="line-clamp-2 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $name }}</span>
</a>
