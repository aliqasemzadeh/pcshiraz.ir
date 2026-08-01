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
    {{ $attributes->class('relative w-full') }}
    x-data="{
        skip: {{ (int) $skip }},
        atBeginning: true,
        atEnd: true,
        resizeObserver: null,
        isRtl() {
            return getComputedStyle(this.$refs.slider).direction === 'rtl'
        },
        init() {
            this.$nextTick(() => {
                this.updateEdgeState()
                this.resizeObserver = new ResizeObserver(() => this.updateEdgeState())
                this.resizeObserver.observe(this.$refs.slider)
            })
        },
        destroy() {
            this.resizeObserver?.disconnect()
        },
        next() {
            this.scrollByDir(1)
        },
        prev() {
            this.scrollByDir(-1)
        },
        scrollByDir(direction) {
            let slider = this.$refs.slider
            let first = slider.firstElementChild
            if (! first) return
            let style = getComputedStyle(slider)
            let gap = parseFloat(style.columnGap || style.gap) || 0
            let amount = (first.getBoundingClientRect().width + gap) * this.skip
            let sign = this.isRtl() ? -1 : 1
            slider.scrollBy({ left: sign * direction * amount, behavior: 'smooth' })
        },
        updateEdgeState() {
            let slider = this.$refs.slider
            if (! slider) return
            let first = slider.firstElementChild
            let last = slider.lastElementChild
            if (! first || ! last) {
                this.atBeginning = true
                this.atEnd = true
                return
            }
            let eps = 4
            if (slider.scrollWidth <= slider.clientWidth + eps) {
                this.atBeginning = true
                this.atEnd = true
                return
            }
            let sr = slider.getBoundingClientRect()
            let fr = first.getBoundingClientRect()
            let lr = last.getBoundingClientRect()
            if (this.isRtl()) {
                this.atBeginning = fr.right >= sr.right - eps
                this.atEnd = lr.left <= sr.left + eps
            } else {
                this.atBeginning = fr.left >= sr.left - eps
                this.atEnd = lr.right <= sr.right + eps
            }
        },
        onKeydown(event) {
            if (event.key === 'ArrowRight') {
                event.preventDefault()
                this.isRtl() ? this.prev() : this.next()
            } else if (event.key === 'ArrowLeft') {
                event.preventDefault()
                this.isRtl() ? this.next() : this.prev()
            }
        },
        focusableWhenVisible: {
            'x-intersect:enter'() {
                this.$el.removeAttribute('tabindex')
            },
            'x-intersect:leave'() {
                this.$el.setAttribute('tabindex', '-1')
            },
        },
    }"
>
    <h2 id="{{ $labelId }}" class="sr-only">{{ $resolvedLabel }}</h2>
    <span id="{{ $contentLabelId }}" class="sr-only">{{ $resolvedLabel }}</span>

    <div
        class="relative"
        x-on:keydown="onKeydown($event)"
        tabindex="0"
        role="region"
        aria-labelledby="{{ $labelId }}"
    >
        <button
            type="button"
            x-on:click="prev"
            class="absolute start-0 top-1/2 z-10 -translate-y-1/2 rounded-full border border-gray-200 bg-white/90 p-2 text-gray-700 shadow-sm backdrop-blur-sm transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-800/90 dark:text-gray-200 dark:hover:bg-gray-800"
            :disabled="atBeginning"
            :aria-disabled="atBeginning"
            :tabindex="atBeginning ? -1 : 0"
        >
            <x-lucide-chevron-left class="h-5 w-5 rtl:rotate-180" />
            <span class="sr-only">{{ __('app.carousel_prev') }}</span>
        </button>

        <button
            type="button"
            x-on:click="next"
            class="absolute end-0 top-1/2 z-10 -translate-y-1/2 rounded-full border border-gray-200 bg-white/90 p-2 text-gray-700 shadow-sm backdrop-blur-sm transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-800/90 dark:text-gray-200 dark:hover:bg-gray-800"
            :disabled="atEnd"
            :aria-disabled="atEnd"
            :tabindex="atEnd ? -1 : 0"
        >
            <x-lucide-chevron-right class="h-5 w-5 rtl:rotate-180" />
            <span class="sr-only">{{ __('app.carousel_next') }}</span>
        </button>

        <ul
            x-ref="slider"
            x-on:scroll.passive="updateEdgeState"
            tabindex="0"
            role="listbox"
            aria-labelledby="{{ $contentLabelId }}"
            class="flex w-full snap-x snap-mandatory gap-3 overflow-x-auto px-10 pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        >
            {{ $slot }}
        </ul>
    </div>
</div>
