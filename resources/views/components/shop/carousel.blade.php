@props([
    'title' => null,
    'skip' => 3,
    'label' => null,
])

@php
    $resolvedLabel = $label ?? $title ?? __('app.carousel');
    $labelId = 'carousel-label-'.uniqid();
    $contentLabelId = 'carousel-content-label-'.uniqid();
@endphp

<div
    {{ $attributes->class('flex w-full flex-col gap-3') }}
    x-data="{
        skip: {{ (int) $skip }},
        atBeginning: true,
        atEnd: false,
        next() {
            this.to((current, offset) => current + (offset * this.skip))
        },
        prev() {
            this.to((current, offset) => current - (offset * this.skip))
        },
        to(strategy) {
            let slider = this.$refs.slider
            let current = slider.scrollLeft
            let first = slider.firstElementChild
            if (! first) return
            let offset = first.getBoundingClientRect().width
            slider.scrollTo({ left: strategy(current, offset), behavior: 'smooth' })
        },
        focusableWhenVisible: {
            'x-intersect:enter'() {
                this.$el.removeAttribute('tabindex')
            },
            'x-intersect:leave'() {
                this.$el.setAttribute('tabindex', '-1')
            },
        },
        disableNextAndPreviousButtons: {
            'x-intersect:enter.threshold.05'() {
                let slideEls = this.$el.parentElement.children
                if (slideEls[0] === this.$el) {
                    this.atBeginning = true
                } else if (slideEls[slideEls.length - 1] === this.$el) {
                    this.atEnd = true
                }
            },
            'x-intersect:leave.threshold.05'() {
                let slideEls = this.$el.parentElement.children
                if (slideEls[0] === this.$el) {
                    this.atBeginning = false
                } else if (slideEls[slideEls.length - 1] === this.$el) {
                    this.atEnd = false
                }
            },
        },
    }"
>
    <div
        x-on:keydown.right="next"
        x-on:keydown.left="prev"
        tabindex="0"
        role="region"
        aria-labelledby="{{ $labelId }}"
        class="flex flex-col gap-3"
    >
        <div class="flex items-center justify-between gap-3">
            @if ($title)
                <h2 id="{{ $labelId }}" class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
            @else
                <h2 id="{{ $labelId }}" class="sr-only">{{ $resolvedLabel }}</h2>
            @endif

            <div @class(['flex gap-2', 'ms-auto' => ! $title])>
                <button
                    type="button"
                    x-on:click="prev"
                    class="rounded-full border border-gray-200 p-2 text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    :disabled="atBeginning"
                    :aria-disabled="atBeginning"
                    :tabindex="atBeginning ? -1 : 0"
                >
                    <x-lucide-chevron-right class="h-5 w-5 rtl:rotate-180" />
                    <span class="sr-only">{{ __('app.carousel_prev') }}</span>
                </button>
                <button
                    type="button"
                    x-on:click="next"
                    class="rounded-full border border-gray-200 p-2 text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    :disabled="atEnd"
                    :aria-disabled="atEnd"
                    :tabindex="atEnd ? -1 : 0"
                >
                    <x-lucide-chevron-left class="h-5 w-5 rtl:rotate-180" />
                    <span class="sr-only">{{ __('app.carousel_next') }}</span>
                </button>
            </div>
        </div>

        <span id="{{ $contentLabelId }}" class="sr-only">{{ $resolvedLabel }}</span>

        <ul
            x-ref="slider"
            tabindex="0"
            role="listbox"
            aria-labelledby="{{ $contentLabelId }}"
            class="flex w-full snap-x snap-mandatory gap-3 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        >
            {{ $slot }}
        </ul>
    </div>
</div>
