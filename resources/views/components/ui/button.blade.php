{{--
  x-ui.button — Flowbite button models (https://flowbite.com/docs/components/buttons/)

  Variants: solid | outline | gradient | gradient-shadow | duotone
  Colors (solid): blue/brand, secondary, tertiary, green/success, red/danger,
                  yellow/warning, dark, ghost, light, purple, cyan, teal, lime, pink, orange
  Duotone keys: purple-blue, cyan-blue, green-blue, purple-pink, pink-orange, teal-lime, red-yellow
  Shape: pill (rounded-full) or default rounded-base
  Loading: spinner + __('general.working') via wire:loading; busy store disables siblings
--}}
@props([
    'color' => 'blue',
    'variant' => null,
    'duotone' => null,
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

    $colorAliases = [
        'brand' => 'blue',
        'success' => 'green',
        'danger' => 'red',
        'warning' => 'yellow',
    ];
    $color = $colorAliases[$color] ?? $color;

    if ($variant === null) {
        $variant = $outline ? 'outline' : 'solid';
    }

    $sizeClasses = match ($size) {
        'icon' => 'p-2 text-sm',
        'xs' => 'px-3 py-1.5 text-xs',
        'sm' => 'px-3 py-2 text-sm',
        'lg' => 'px-5 py-3 text-base',
        'xl' => 'px-6 py-3.5 text-base',
        default => 'px-4 py-2.5 text-sm',
    };

    $roundedClasses = $pill ? 'rounded-full' : 'rounded-base';

    $baseClasses = 'box-border font-medium leading-5 focus:outline-none text-center inline-flex items-center justify-center';

    $solidClasses = match ($color) {
        'secondary' => 'text-body bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary shadow-xs',
        'tertiary' => 'text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary-soft shadow-xs',
        'green' => 'text-white bg-success border border-transparent hover:bg-success-strong focus:ring-4 focus:ring-success-medium shadow-xs',
        'red' => 'text-white bg-danger border border-transparent hover:bg-danger-strong focus:ring-4 focus:ring-danger-medium shadow-xs',
        'yellow' => 'text-white bg-warning border border-transparent hover:bg-warning-strong focus:ring-4 focus:ring-warning-medium shadow-xs',
        'dark' => 'text-white bg-dark border border-transparent hover:bg-dark-strong focus:ring-4 focus:ring-neutral-tertiary shadow-xs',
        'ghost' => 'text-heading bg-transparent border border-transparent hover:bg-neutral-secondary-medium focus:ring-4 focus:ring-neutral-tertiary',
        'light' => 'text-body bg-neutral-primary-soft border border-default hover:bg-neutral-secondary-medium hover:text-heading focus:ring-4 focus:ring-neutral-tertiary-soft shadow-xs',
        'purple' => 'text-white bg-purple-700 border border-transparent hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 dark:bg-purple-600 dark:hover:bg-purple-700 dark:focus:ring-purple-800 shadow-xs',
        'cyan' => 'text-white bg-cyan-700 border border-transparent hover:bg-cyan-800 focus:ring-4 focus:ring-cyan-300 dark:bg-cyan-600 dark:hover:bg-cyan-700 dark:focus:ring-cyan-800 shadow-xs',
        'teal' => 'text-white bg-teal-700 border border-transparent hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800 shadow-xs',
        'lime' => 'text-heading bg-lime-400 border border-transparent hover:bg-lime-500 focus:ring-4 focus:ring-lime-300 dark:focus:ring-lime-800 shadow-xs',
        'pink' => 'text-white bg-pink-700 border border-transparent hover:bg-pink-800 focus:ring-4 focus:ring-pink-300 dark:bg-pink-600 dark:hover:bg-pink-700 dark:focus:ring-pink-800 shadow-xs',
        'orange' => 'text-white bg-orange-700 border border-transparent hover:bg-orange-800 focus:ring-4 focus:ring-orange-300 dark:bg-orange-600 dark:hover:bg-orange-700 dark:focus:ring-orange-800 shadow-xs',
        default => 'text-white bg-brand border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs',
    };

    $outlineClasses = match ($color) {
        'green' => 'text-success bg-neutral-primary border border-success hover:bg-success hover:text-white focus:ring-4 focus:ring-neutral-tertiary',
        'red' => 'text-danger bg-neutral-primary border border-danger hover:bg-danger hover:text-white focus:ring-4 focus:ring-neutral-tertiary',
        'yellow' => 'text-warning bg-neutral-primary border border-warning hover:bg-warning hover:text-white focus:ring-4 focus:ring-neutral-tertiary',
        'purple' => 'text-purple-700 hover:text-white border border-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 dark:border-purple-500 dark:text-purple-500 dark:hover:text-white dark:hover:bg-purple-500 dark:focus:ring-purple-800',
        'dark', 'light', 'secondary', 'tertiary', 'ghost' => 'text-body bg-neutral-primary border border-default hover:bg-neutral-secondary-soft hover:text-heading focus:ring-4 focus:ring-neutral-tertiary',
        'cyan' => 'text-cyan-700 bg-neutral-primary border border-cyan-700 hover:bg-cyan-700 hover:text-white focus:ring-4 focus:ring-cyan-300 dark:border-cyan-500 dark:text-cyan-500 dark:hover:bg-cyan-600 dark:hover:text-white',
        'teal' => 'text-teal-700 bg-neutral-primary border border-teal-700 hover:bg-teal-700 hover:text-white focus:ring-4 focus:ring-teal-300 dark:border-teal-500 dark:text-teal-500 dark:hover:bg-teal-600 dark:hover:text-white',
        'lime' => 'text-lime-700 bg-neutral-primary border border-lime-700 hover:bg-lime-500 hover:text-heading focus:ring-4 focus:ring-lime-300',
        'pink' => 'text-pink-700 bg-neutral-primary border border-pink-700 hover:bg-pink-700 hover:text-white focus:ring-4 focus:ring-pink-300 dark:border-pink-500 dark:text-pink-500 dark:hover:bg-pink-600 dark:hover:text-white',
        'orange' => 'text-orange-700 bg-neutral-primary border border-orange-700 hover:bg-orange-700 hover:text-white focus:ring-4 focus:ring-orange-300 dark:border-orange-500 dark:text-orange-500 dark:hover:bg-orange-600 dark:hover:text-white',
        default => 'text-fg-brand bg-neutral-primary border border-brand hover:bg-brand hover:text-white focus:ring-4 focus:ring-brand-subtle',
    };

    $gradientClasses = match ($color) {
        'green' => 'text-white bg-gradient-to-r from-green-400 via-green-500 to-green-600 hover:bg-gradient-to-br focus:ring-4 focus:ring-green-300 dark:focus:ring-green-800',
        'cyan' => 'text-white bg-gradient-to-r from-cyan-400 via-cyan-500 to-cyan-600 hover:bg-gradient-to-br focus:ring-4 focus:ring-cyan-300 dark:focus:ring-cyan-800',
        'teal' => 'text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br focus:ring-4 focus:ring-teal-300 dark:focus:ring-teal-800',
        'lime' => 'text-heading bg-gradient-to-r from-lime-200 via-lime-400 to-lime-500 hover:bg-gradient-to-br focus:ring-4 focus:ring-lime-300 dark:focus:ring-lime-800',
        'red' => 'text-white bg-gradient-to-r from-red-400 via-red-500 to-red-600 hover:bg-gradient-to-br focus:ring-4 focus:ring-red-300 dark:focus:ring-red-800',
        'pink' => 'text-white bg-gradient-to-r from-pink-400 via-pink-500 to-pink-600 hover:bg-gradient-to-br focus:ring-4 focus:ring-pink-300 dark:focus:ring-pink-800',
        'purple' => 'text-white bg-gradient-to-r from-purple-500 via-purple-600 to-purple-700 hover:bg-gradient-to-br focus:ring-4 focus:ring-purple-300 dark:focus:ring-purple-800',
        'orange' => 'text-white bg-gradient-to-r from-orange-400 via-orange-500 to-orange-600 hover:bg-gradient-to-br focus:ring-4 focus:ring-orange-300 dark:focus:ring-orange-800',
        'yellow' => 'text-heading bg-gradient-to-r from-yellow-200 via-yellow-400 to-yellow-500 hover:bg-gradient-to-br focus:ring-4 focus:ring-yellow-300 dark:focus:ring-yellow-800',
        default => 'text-white bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 hover:bg-gradient-to-br focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800',
    };

    $gradientShadowClasses = match ($color) {
        'green' => 'shadow-lg shadow-green-500/50 dark:shadow-lg dark:shadow-green-800/80',
        'cyan' => 'shadow-lg shadow-cyan-500/50 dark:shadow-lg dark:shadow-cyan-800/80',
        'teal' => 'shadow-lg shadow-teal-500/50 dark:shadow-lg dark:shadow-teal-800/80',
        'lime' => 'shadow-lg shadow-lime-500/50 dark:shadow-lg dark:shadow-lime-800/80',
        'red' => 'shadow-lg shadow-red-500/50 dark:shadow-lg dark:shadow-red-800/80',
        'pink' => 'shadow-lg shadow-pink-500/50 dark:shadow-lg dark:shadow-pink-800/80',
        'purple' => 'shadow-lg shadow-purple-500/50 dark:shadow-lg dark:shadow-purple-800/80',
        'orange' => 'shadow-lg shadow-orange-500/50 dark:shadow-lg dark:shadow-orange-800/80',
        'yellow' => 'shadow-lg shadow-yellow-500/50 dark:shadow-lg dark:shadow-yellow-800/80',
        default => 'shadow-lg shadow-blue-500/50 dark:shadow-lg dark:shadow-blue-800/80',
    };

    $duotoneKey = $duotone ?? 'purple-blue';
    $duotoneClasses = match ($duotoneKey) {
        'cyan-blue' => 'text-white bg-gradient-to-r from-cyan-500 to-blue-500 hover:bg-gradient-to-bl focus:ring-4 focus:ring-cyan-300 dark:focus:ring-cyan-800',
        'green-blue' => 'text-white bg-gradient-to-br from-green-400 to-blue-600 hover:bg-gradient-to-bl focus:ring-4 focus:ring-green-200 dark:focus:ring-green-800',
        'purple-pink' => 'text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:bg-gradient-to-l focus:ring-4 focus:ring-purple-200 dark:focus:ring-purple-800',
        'pink-orange' => 'text-white bg-gradient-to-br from-pink-500 to-orange-400 hover:bg-gradient-to-bl focus:ring-4 focus:ring-pink-200 dark:focus:ring-pink-800',
        'teal-lime' => 'text-heading bg-gradient-to-r from-teal-200 to-lime-200 hover:bg-gradient-to-l hover:from-teal-200 hover:to-lime-200 focus:ring-4 focus:ring-lime-200 dark:focus:ring-teal-700',
        'red-yellow' => 'text-heading bg-gradient-to-r from-red-200 via-red-300 to-yellow-200 hover:bg-gradient-to-bl focus:ring-4 focus:ring-red-100 dark:focus:ring-red-400',
        default => 'text-white bg-gradient-to-br from-purple-600 to-blue-500 hover:bg-gradient-to-bl focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800',
    };

    $colorClasses = match ($variant) {
        'outline' => $outlineClasses,
        'gradient' => $gradientClasses,
        'gradient-shadow' => $gradientClasses.' '.$gradientShadowClasses,
        'duotone' => $duotoneClasses,
        default => $solidClasses,
    };

    $isLightSpinner = in_array($variant, ['outline'], true)
        || in_array($color, ['light', 'secondary', 'tertiary', 'ghost', 'lime'], true)
        || ($variant === 'duotone' && in_array($duotoneKey, ['teal-lime', 'red-yellow'], true))
        || ($variant === 'gradient' && in_array($color, ['lime', 'yellow'], true));

    $spinnerColor ??= $isLightSpinner ? 'blue' : 'white';

    $tag = $href ? 'a' : 'button';
    $disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed' : '';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    @if ($tag === 'button') type="{{ $type }}" @endif
    @if ($disabled) disabled @endif
    wire:loading.attr="disabled"
    x-bind:disabled="Boolean($store.ui?.busy)"
    {{ $attributes->class([$baseClasses, $sizeClasses, $colorClasses, $roundedClasses, $disabledClasses]) }}
>
    @isset($icon)
        {{ $icon }}
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
</{{ $tag }}>
