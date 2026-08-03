<div>
    <x-fwb.select
        wire:model="form.brand_id"
        :label="__('general.brand')"
        :options="$brands"
    />
    @error('form.brand_id')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.select
        wire:model="form.category_id"
        :label="__('general.category')"
        :options="$categories"
    />
    @error('form.category_id')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.select
        wire:model="form.item_type"
        :label="__('general.item_type')"
        :options="$itemTypes"
    />
    @error('form.item_type')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.select
        wire:model="form.group_id"
        :label="__('general.group')"
        :options="$groups"
    />
    @error('form.group_id')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.checkbox
        wire:model="form.is_main"
        :label="__('general.is_main')"
    />
    @error('form.is_main')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.checkbox wire:model="form.is_active" :label="__('app.is_active')" />
</div>
<div>
    <x-fwb.checkbox wire:model="form.is_purchasable" :label="__('app.is_purchasable')" />
</div>
<div>
    <x-fwb.input
        wire:model="form.stock"
        :label="__('app.stock')"
        type="number"
        min="0"
        dir="ltr"
    />
    @error('form.stock')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>
<div>
    <x-fwb.checkbox wire:model="form.is_contact_price" :label="__('app.is_contact_price')" />
</div>

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
    <x-fwb.input
        wire:model="form.slug"
        :label="__('general.slug')"
        type="text"
        dir="ltr"
    />
    @error('form.slug')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.textarea
        wire:model="form.description"
        :label="__('general.description')"
        :rows="4"
    />
    @error('form.description')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

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
        <x-fwb.input
            wire:model="form.color_code"
            :label="__('general.color_code')"
            type="text"
            dir="ltr"
            placeholder="#000000"
        />
        @error('form.color_code')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <x-fwb.input wire:model="form.weight" :label="__('general.weight')" type="number" min="0" />
        @error('form.weight')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-fwb.input wire:model="form.length" :label="__('general.length')" type="number" min="0" />
        @error('form.length')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-fwb.input wire:model="form.width" :label="__('general.width')" type="number" min="0" />
        @error('form.width')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-fwb.input wire:model="form.height" :label="__('general.height')" type="number" min="0" />
        @error('form.height')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <x-fwb.input
        wire:model="form.seo_title"
        :label="__('general.seo_title')"
        type="text"
    />
    @error('form.seo_title')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.textarea
        wire:model="form.meta_description"
        :label="__('general.meta_description')"
        :rows="3"
    />
    @error('form.meta_description')
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
                        wire:click="removeTag('{{ $tag }}')"
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

    @if (count($existingTags) > 0)
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

<div>
    <x-ui.file-input
        wire:model="form.product_image"
        :label="__('general.product_image')"
        accept="image/jpeg,image/png,image/webp,image/avif"
        dropzone
        :preview="!empty($currentImageUrl) ? $currentImageUrl : (!empty($form->remote_image_url) ? $form->remote_image_url : null)"
    />
    @error('form.product_image')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>
