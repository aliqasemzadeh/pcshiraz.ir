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
        dragging: false,
        dragPointerId: null,
        dragStartX: 0,
        dragStartScroll: 0,
        dragMoved: false,
        isRtl() {
            return getComputedStyle(this.$refs.slider).direction === 'rtl'
        },
        init() {
            this.$nextTick(() => {
                this.updateEdgeState()
                this.resizeObserver = new ResizeObserver(() => this.updateEdgeState())
                this.resizeObserver.observe(this.$refs.slider)
                if (this.$refs.slider.firstElementChild) {
                    this.resizeObserver.observe(this.$refs.slider.firstElementChild)
                }
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

            let eps = 2
            let max = slider.scrollWidth - slider.clientWidth

            if (max <= eps) {
                this.atBeginning = true
                this.atEnd = true
                return
            }

            // Modern RTL engines: scrollLeft goes from 0 toward -max
            let position = Math.abs(slider.scrollLeft)
            this.atBeginning = position <= eps
            this.atEnd = position >= max - eps
        },
        onPointerDown(event) {
            if (event.pointerType === 'touch' || event.button !== 0) return

            this.dragPointerId = event.pointerId
            this.dragStartX = event.clientX
            this.dragStartScroll = this.$refs.slider.scrollLeft
            this.dragMoved = false
            this.dragging = true
            this.$refs.slider.setPointerCapture(event.pointerId)
        },
        onPointerMove(event) {
            if (! this.dragging || event.pointerId !== this.dragPointerId) return

            let delta = event.clientX - this.dragStartX
            if (Math.abs(delta) > 5) this.dragMoved = true

            this.$refs.slider.scrollLeft = this.dragStartScroll - delta
            event.preventDefault()
        },
        onPointerUp(event) {
            if (! this.dragging) return

            this.$refs.slider.releasePointerCapture?.(event.pointerId)
            this.dragging = false
            this.dragPointerId = null
            this.updateEdgeState()
        },
        onClickCapture(event) {
            if (! this.dragMoved) return

            event.preventDefault()
            event.stopPropagation()
            this.dragMoved = false
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
            x-on:pointerdown="onPointerDown($event)"
            x-on:pointermove="onPointerMove($event)"
            x-on:pointerup="onPointerUp($event)"
            x-on:pointercancel="onPointerUp($event)"
            x-on:click.capture="onClickCapture($event)"
            x-on:dragstart.prevent
            :style="dragging ? 'scroll-snap-type: none' : null"
            :class="dragging ? 'cursor-grabbing select-none' : 'cursor-grab'"
            tabindex="0"
            role="listbox"
            aria-labelledby="{{ $contentLabelId }}"
            class="flex w-full snap-x snap-mandatory gap-3 overflow-x-auto overscroll-x-contain px-10 pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        >
            {{ $slot }}
        </ul>
    </div>
</div>
