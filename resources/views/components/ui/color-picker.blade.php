@props([
    'existingColors' => [],
])

<div {{ $attributes->class(['space-y-3']) }}>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <x-fwb.input
                wire:model="form.color_name"
                :label="__('general.color_name')"
                type="text"
            />
            @error('form.color_name')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium text-heading">
                {{ __('general.color_code') }}
            </label>
            <div class="flex items-center gap-2">
                <input
                    type="color"
                    wire:model.live="form.color_code"
                    class="h-10 w-12 shrink-0 cursor-pointer rounded-base border border-default-medium bg-neutral-primary-soft p-1"
                />
                <div class="min-w-0 flex-1">
                    <x-fwb.input
                        wire:model="form.color_code"
                        type="text"
                        dir="ltr"
                        placeholder="#000000"
                    />
                </div>
            </div>
            @error('form.color_code')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    @if (count($existingColors) > 1)
        <div class="flex gap-2">
            <div class="flex-1">
                <x-fwb.select
                    wire:model="selectedExistingColor"
                    :label="__('general.select_existing_color')"
                    :options="$existingColors"
                />
            </div>
            <div class="pt-7">
                <x-ui.button type="button" color="light" outline :loading="false" wire:click="applyExistingColor">
                    {{ __('general.apply_color') }}
                </x-ui.button>
            </div>
        </div>
    @endif
</div>
