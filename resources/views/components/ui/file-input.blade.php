@props([
    'label' => null,
    'helper' => null,
    'multiple' => false,
    'size' => 'md',
    'disabled' => false,
    'required' => false,
    'dropzone' => false,
    'id' => null,
])

@php
    $inputId = $id ?: 'file-input-'.uniqid();
@endphp

<div
    x-data="{ uploading: false, progress: 0 }"
    x-on:livewire-upload-start="uploading = true; progress = 0; $store.ui.start()"
    x-on:livewire-upload-finish="uploading = false; progress = 0; $store.ui.end()"
    x-on:livewire-upload-cancel="uploading = false; progress = 0; $store.ui.end()"
    x-on:livewire-upload-error="uploading = false; progress = 0; $store.ui.end()"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
>
    @if ($dropzone)
        <div>
            @if ($label)
                <label class="mb-2 block text-sm font-medium text-heading" for="{{ $inputId }}">{{ $label }}</label>
            @endif

            <label
                for="{{ $inputId }}"
                @class([
                    'flex w-full cursor-pointer flex-col items-center justify-center rounded-base border-2 border-dashed border-default-medium bg-neutral-secondary-soft hover:bg-neutral-secondary-medium',
                    'pointer-events-none opacity-60' => $disabled,
                    'h-48' => true,
                ])
            >
                <div class="flex flex-col items-center justify-center px-4 pt-5 pb-6 text-center">
                    <x-lucide-upload-cloud class="mb-3 h-8 w-8 text-body" />
                    <p class="mb-1 text-sm text-body">
                        <span class="font-semibold">{{ __('app.file_click_or_drop') }}</span>
                    </p>
                    @if ($helper)
                        <p class="text-xs text-body">{{ $helper }}</p>
                    @endif
                </div>
                <input
                    id="{{ $inputId }}"
                    type="file"
                    class="hidden"
                    @if ($multiple) multiple @endif
                    @if ($disabled) disabled @endif
                    @if ($required) required @endif
                    {{ $attributes->merge([
                        'x-bind:disabled' => 'Boolean($store.ui?.busy) && !uploading',
                    ]) }}
                />
            </label>
        </div>
    @else
        <x-fwb.file-input
            :label="$label"
            :helper="$helper"
            :multiple="$multiple"
            :size="$size"
            :disabled="$disabled"
            :required="$required"
            :dropzone="false"
            :id="$inputId"
            :attributes="$attributes->merge([
                'x-bind:disabled' => 'Boolean($store.ui?.busy) && !uploading',
            ])"
        />
    @endif

    <div x-show="uploading" x-cloak class="mt-3 space-y-1" aria-live="polite">
        <div class="mb-1 flex justify-between">
            <span class="text-sm font-medium text-body">{{ __('general.uploading') }}</span>
            <span class="text-sm font-medium text-body" x-text="Math.round(progress) + '%'"></span>
        </div>
        <div class="h-2.5 w-full rounded-full bg-neutral-quaternary">
            <div
                class="h-2.5 rounded-full bg-brand transition-all duration-150"
                :style="'width: ' + progress + '%'"
            ></div>
        </div>
    </div>
</div>
