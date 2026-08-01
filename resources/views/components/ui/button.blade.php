@props([
    'color' => 'blue',
    'size' => 'md',
    'type' => 'button',
    'target' => null,
    'loadingText' => null,
    'outline' => false,
    'pill' => false,
    'disabled' => false,
    'href' => null,
    'spinnerColor' => null,
    'loading' => true,
])

@php
    $loadingText ??= __('general.working');
    $spinnerColor ??= ($outline || $color === 'light') ? 'blue' : 'white';
@endphp

<x-fwb.button
    :color="$color"
    :size="$size"
    :outline="$outline"
    :pill="$pill"
    :disabled="$disabled"
    :href="$href"
    type="{{ $type }}"
    wire:loading.attr="disabled"
    x-bind:disabled="$store.ui.busy"
    {{ $attributes->class(['justify-center']) }}
>
    @isset($icon)
        <x-slot:icon>{{ $icon }}</x-slot:icon>
    @endisset

    @if ($loading)
        <span
            @if ($target) wire:loading.remove wire:target="{{ $target }}" @else wire:loading.remove @endif
            class="inline-flex items-center"
        >
            {{ $slot }}
        </span>

            <span
                @if ($target) wire:loading.inline-flex wire:target="{{ $target }}" @else wire:loading.inline-flex @endif
                class="items-center justify-center gap-2"
            >
            <x-fwb.spinner :color="$spinnerColor" size="xs" />
            <span>{{ $loadingText }}</span>
        </span>
    @else
        <span class="inline-flex items-center">{{ $slot }}</span>
    @endif
</x-fwb.button>
