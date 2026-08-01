@props([
    'tags' => [],
])

@if (count($tags) > 0)
    <div
        x-data="{
            skip: 3,
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
                let offset = slider.firstElementChild.getBoundingClientRect().width
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
        class="flex w-full flex-col gap-3"
    >
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.popular_tags') }}</h2>
            <div class="flex gap-2">
                <button
                    type="button"
                    x-on:click="prev"
                    class="rounded-full border border-gray-200 p-2 text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    :disabled="atBeginning"
                    :aria-disabled="atBeginning"
                >
                    <x-lucide-chevron-right class="h-5 w-5 rtl:rotate-180" />
                    <span class="sr-only">prev</span>
                </button>
                <button
                    type="button"
                    x-on:click="next"
                    class="rounded-full border border-gray-200 p-2 text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    :disabled="atEnd"
                    :aria-disabled="atEnd"
                >
                    <x-lucide-chevron-left class="h-5 w-5 rtl:rotate-180" />
                    <span class="sr-only">next</span>
                </button>
            </div>
        </div>

        <ul
            x-ref="slider"
            tabindex="0"
            role="listbox"
            class="flex w-full snap-x snap-mandatory gap-3 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        >
            @foreach ($tags as $tag)
                <li
                    x-bind="disableNextAndPreviousButtons"
                    class="w-1/3 shrink-0 snap-start sm:w-1/4 md:w-1/5 lg:w-1/6"
                    role="option"
                >
                    <a
                        href="{{ route('shop.tag', $tag['slug']) }}"
                        wire:navigate
                        x-bind="focusableWhenVisible"
                        class="flex aspect-square flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-3 text-center shadow-sm transition hover:border-brand/40 hover:shadow dark:border-gray-700 dark:from-gray-800 dark:to-gray-900"
                    >
                        <span class="line-clamp-2 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $tag['name'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
