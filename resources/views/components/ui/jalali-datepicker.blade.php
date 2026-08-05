@props([
    'label' => null,
    'placeholder' => '1400/01/01',
    'maxDate' => null,
    'minDate' => null,
    'disabled' => false,
    'readonly' => false,
    'id' => null,
])

@php
    $wireModel = $attributes->wire('model')->value();
    $livewireId = \Livewire\Livewire::current()?->getId() ?? 'x';
    $inputId = $id ?: 'jalali-datepicker-'.$livewireId.'-'.str_replace(['.', '[', ']'], '-', (string) ($wireModel ?: 'date'));
    $monthNames = [
        __('general.jalali_month_1'),
        __('general.jalali_month_2'),
        __('general.jalali_month_3'),
        __('general.jalali_month_4'),
        __('general.jalali_month_5'),
        __('general.jalali_month_6'),
        __('general.jalali_month_7'),
        __('general.jalali_month_8'),
        __('general.jalali_month_9'),
        __('general.jalali_month_10'),
        __('general.jalali_month_11'),
        __('general.jalali_month_12'),
    ];
    $weekdayNames = [
        __('general.jalali_weekday_0'),
        __('general.jalali_weekday_1'),
        __('general.jalali_weekday_2'),
        __('general.jalali_weekday_3'),
        __('general.jalali_weekday_4'),
        __('general.jalali_weekday_5'),
        __('general.jalali_weekday_6'),
    ];
    $alpineConfig = [
        'wireProperty' => $wireModel,
        'maxDate' => $maxDate,
        'minDate' => $minDate,
        'disabled' => (bool) $disabled,
        'readonly' => (bool) $readonly,
        'monthNames' => $monthNames,
        'weekdayNames' => $weekdayNames,
        'todayLabel' => __('general.today'),
        'clearLabel' => __('general.clear'),
    ];
@endphp

<div
    x-data="jalaliDatepicker(@js($alpineConfig))"
    x-on:keydown.escape.window="open && close()"
    @class($attributes->get('class'))
>
    @if ($label)
        <label for="{{ $inputId }}" class="mb-2 block text-sm font-medium text-heading">{{ $label }}</label>
    @endif

    <div class="relative">
        <input
            x-ref="input"
            type="hidden"
            id="{{ $inputId }}"
            @if ($wireModel) wire:model="{{ $wireModel }}" @endif
        />

        <input
            type="text"
            dir="ltr"
            autocomplete="off"
            placeholder="{{ $placeholder }}"
            class="block w-full rounded-base border border-default-medium bg-neutral-primary px-3 py-2.5 text-sm text-heading placeholder:text-body focus:border-brand focus:ring-brand disabled:cursor-not-allowed disabled:opacity-60 read-only:cursor-default read-only:opacity-70"
            x-bind:value="displayValue"
            x-bind:readonly="readonly || disabled"
            x-bind:disabled="disabled"
            x-on:focus="canOpen() && (open = true)"
            x-on:click="toggle()"
        />

        <button
            type="button"
            class="absolute inset-y-0 end-0 flex items-center pe-3 text-body hover:text-heading disabled:pointer-events-none"
            x-bind:disabled="disabled || readonly"
            x-on:click.stop="toggle()"
            tabindex="-1"
        >
            <x-lucide-calendar class="h-4 w-4" />
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-on:click.outside="close()"
            class="absolute top-full z-50 mt-1 w-[min(100%,20rem)] rounded-lg border border-default-medium bg-neutral-primary p-3 shadow-lg"
        >
            <div class="mb-3 flex items-center justify-between gap-2">
                <button
                    type="button"
                    class="rounded-base p-1.5 text-body hover:bg-neutral-secondary-medium hover:text-heading"
                    x-on:click="prevMonth()"
                >
                    <x-lucide-chevron-right class="h-4 w-4" />
                </button>

                <div class="flex min-w-0 flex-1 items-center justify-center gap-2">
                    <select
                        x-model.number="viewMonth"
                        class="max-w-[7rem] rounded-base border border-default-medium bg-neutral-primary px-2 py-1 text-sm text-heading focus:border-brand focus:ring-brand"
                    >
                        <template x-for="(name, index) in monthNames" :key="index">
                            <option :value="index + 1" x-text="name"></option>
                        </template>
                    </select>

                    <select
                        x-model.number="viewYear"
                        class="w-20 rounded-base border border-default-medium bg-neutral-primary px-2 py-1 text-sm text-heading focus:border-brand focus:ring-brand"
                    >
                        <template x-for="year in years" :key="year">
                            <option :value="year" x-text="year"></option>
                        </template>
                    </select>
                </div>

                <button
                    type="button"
                    class="rounded-base p-1.5 text-body hover:bg-neutral-secondary-medium hover:text-heading"
                    x-on:click="nextMonth()"
                >
                    <x-lucide-chevron-left class="h-4 w-4" />
                </button>
            </div>

            <div class="mb-1 grid grid-cols-7 gap-1 text-center text-xs font-medium text-body">
                <template x-for="(name, index) in weekdayNames" :key="index">
                    <div class="py-1" x-text="name"></div>
                </template>
            </div>

            <div class="grid grid-cols-7 gap-1">
                <template x-for="day in calendarDays" :key="day.key">
                    <button
                        type="button"
                        class="flex h-8 w-full items-center justify-center rounded-base text-sm transition"
                        x-bind:class="{
                            'text-body/40': !day.inMonth,
                            'bg-brand text-white': day.selected,
                            'ring-1 ring-brand/40': day.today && !day.selected,
                            'hover:bg-neutral-secondary-medium': day.inMonth && !day.selected && !day.disabled,
                            'cursor-not-allowed opacity-30': day.disabled,
                        }"
                        x-bind:disabled="day.disabled"
                        x-on:click="selectDate(day)"
                        x-text="day.label"
                    ></button>
                </template>
            </div>

            <div class="mt-3 flex items-center justify-between gap-2 border-t border-default-medium pt-3">
                <button
                    type="button"
                    class="rounded-base bg-brand px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-strong"
                    x-on:click="selectToday()"
                    x-text="todayLabel"
                ></button>

                <button
                    type="button"
                    class="rounded-base border border-default-medium px-3 py-1.5 text-xs font-medium text-body hover:bg-neutral-secondary-medium hover:text-heading"
                    x-on:click="clear()"
                    x-text="clearLabel"
                ></button>
            </div>
        </div>
    </div>
</div>
