@php
    /** @var array<string|int, string> $existingTags */
    $existingTags = $existingTags ?? [];
    $currentImageUrl = $currentImageUrl ?? null;
@endphp

<div>
    <x-fwb.input
        wire:model="form.title"
        :label="__('general.title')"
        type="text"
    />
    @error('form.title')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.textarea
        wire:model="form.body"
        :label="__('general.body')"
        rows="8"
    />
    @error('form.body')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.checkbox wire:model="form.is_active" :label="__('general.active')" />
    @error('form.is_active')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-3">
    <label class="block text-sm font-medium text-heading">{{ __('general.tags') }}</label>

    @if (count($form->tags) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach ($form->tags as $tag)
                <span class="inline-flex items-center gap-1 rounded-base bg-neutral-secondary-soft px-2 py-1 text-sm text-body">
                    {{ $tag }}
                    <button
                        type="button"
                        wire:click="removeTag(@js($tag))"
                        class="text-fg-danger-strong hover:opacity-80"
                    >
                        ×
                    </button>
                </span>
            @endforeach
        </div>
    @endif

    <div class="flex gap-2">
        <div class="flex-1">
            <x-fwb.input
                wire:model="form.tag_input"
                :label="__('general.add_tag')"
                type="text"
                wire:keydown.enter.prevent="addTag"
            />
        </div>
        <div class="pt-7">
            <x-ui.button type="button" color="cyan" :loading="false" wire:click="addTag">
                {{ __('general.add_tag') }}
            </x-ui.button>
        </div>
    </div>

    @if (count($existingTags) > 1)
        <div class="flex gap-2">
            <div class="flex-1">
                <x-fwb.select
                    wire:model="selectedExistingTag"
                    :label="__('general.tags')"
                    :options="$existingTags"
                />
            </div>
            <div class="pt-7">
                <x-ui.button type="button" color="light" outline :loading="false" wire:click="addExistingTag">
                    {{ __('general.add_tag') }}
                </x-ui.button>
            </div>
        </div>
    @endif

    @error('form.tags')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

@if ($currentImageUrl)
    <div class="flex items-center gap-3 rounded-lg border border-default bg-neutral-secondary-soft p-3">
        <img
            src="{{ $currentImageUrl }}"
            alt="{{ $form->title }}"
            class="h-16 w-28 rounded object-cover"
        >
        <span class="text-sm text-body">{{ __('general.image') }}</span>
    </div>
@endif

<div>
    <x-ui.file-input
        wire:model="form.image"
        :label="__('general.image')"
        accept="image/jpeg,image/png,image/webp,image/avif"
    />
    @error('form.image')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>
