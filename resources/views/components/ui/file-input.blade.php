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

<div
    x-data="{ uploading: false, progress: 0 }"
    x-on:livewire-upload-start="uploading = true; progress = 0; $store.ui.start()"
    x-on:livewire-upload-finish="uploading = false; progress = 0; $store.ui.end()"
    x-on:livewire-upload-cancel="uploading = false; progress = 0; $store.ui.end()"
    x-on:livewire-upload-error="uploading = false; progress = 0; $store.ui.end()"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
>
    <x-fwb.file-input
        :label="$label"
        :helper="$helper"
        :multiple="$multiple"
        :size="$size"
        :disabled="$disabled"
        :required="$required"
        :dropzone="$dropzone"
        :id="$id"
        :attributes="$attributes->merge([
            'x-bind:disabled' => '$store.ui.busy && !uploading',
        ])"
    />

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
