@props([
    'label' => null,
    'helper' => null,
    'multiple' => false,
    'size' => 'md',
    'disabled' => false,
    'required' => false,
    'dropzone' => false,
    'id' => null,
    'preview' => null,
])

@php
    $inputId = $id ?: 'file-input-'.uniqid();
    $wireModel = $attributes->wire('model')->value();
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
                    'relative flex w-full cursor-pointer flex-col items-center justify-center rounded-base border-2 border-dashed border-default-medium bg-neutral-secondary-soft hover:bg-neutral-secondary-medium',
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
                    {{ $attributes->whereDoesntStartWith('class') }}
                    x-bind:disabled="Boolean($store.ui?.busy) && !uploading"
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
            {{ $attributes->whereDoesntStartWith('class') }}
            x-bind:disabled="Boolean($store.ui?.busy) && !uploading"
        />
    @endif

    {{-- Preview --}}
    @if ($wireModel)
        @php
            $file = $this->get($wireModel);
            $files = $multiple ? (is_array($file) ? $file : ($file ? [$file] : [])) : ($file ? [$file] : []);
        @endphp

        @if (!empty($files) || $preview)
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                {{-- Existing Preview (from URL) --}}
                @if ($preview && empty($files))
                    <div class="group relative aspect-square overflow-hidden rounded-lg border border-default bg-neutral-secondary-soft">
                        <img src="{{ $preview }}" class="h-full w-full object-cover">
                    </div>
                @endif

                {{-- Uploaded Files Previews --}}
                @foreach ($files as $f)
                    @if ($f instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                        <div class="group relative aspect-square overflow-hidden rounded-lg border border-default bg-neutral-secondary-soft">
                            @if (str_starts_with($f->getMimeType(), 'image/'))
                                <img src="{{ $f->temporaryUrl() }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full flex-col items-center justify-center p-2 text-center">
                                    <x-lucide-file class="mb-1 h-8 w-8 text-body" />
                                    <span class="truncate text-[10px] text-body">{{ $f->getClientOriginalName() }}</span>
                                </div>
                            @endif

                            <button
                                type="button"
                                wire:click="$set('{{ $wireModel }}', {{ $multiple ? 'array_diff('.$wireModel.', [\''.$f->getFilename().'\'])' : 'null' }})"
                                class="absolute top-1 right-1 hidden rounded-full bg-red-600 p-1 text-white hover:bg-red-700 group-hover:block"
                            >
                                <x-lucide-x class="h-3 w-3" />
                            </button>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
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
