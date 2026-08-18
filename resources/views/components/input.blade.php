@props([
    'money' => false,
    'label' => null,
])

@if ($money)
    <x-fwb.input
        :label="$label"
        type="text"
        inputmode="numeric"
        dir="ltr"
        x-mask:dynamic="\$money(\$input, '.', ',', 0)"
        x-init="\$el.dispatchEvent(new Event('input'))"
        {{ $attributes->except(['type', 'inputmode', 'dir']) }}
    />
@else
    <x-fwb.input
        :label="$label"
        {{ $attributes }}
    />
@endif
