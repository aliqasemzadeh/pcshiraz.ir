@props([
    'money' => false,
    'label' => null,
])

@php
    $moneyAttributes = $money ? [
        'type' => 'text',
        'inputmode' => 'numeric',
        'dir' => 'ltr',
        'x-mask:dynamic' => '$money($input, \'.\', \',\', 0)',
        'x-init' => '$el.dispatchEvent(new Event(\'input\'))',
    ] : [];
@endphp

<x-fwb.input
    :label="$label"
    {{ $attributes->except($money ? ['type', 'inputmode', 'dir'] : [])->merge($moneyAttributes) }}
/>
