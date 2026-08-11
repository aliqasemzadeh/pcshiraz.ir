<div class="space-y-4">
    <div>
        <x-fwb.input
            wire:model="form.title"
            :label="__('app.address_title')"
            type="text"
            :placeholder="__('app.address_title_placeholder')"
        />
        @error('form.title')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-fwb.input
            wire:model="form.postal_code"
            :label="__('app.postal_code')"
            type="text"
            dir="ltr"
            inputmode="numeric"
            maxlength="10"
        />
        @error('form.postal_code')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-fwb.select
            wire:model.live="form.province_id"
            :label="__('app.province')"
            :options="$provinces"
        />
        @error('form.province_id')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-fwb.select
            wire:model="form.city_id"
            :label="__('app.city')"
            :options="$cities"
            :disabled="blank($form->province_id)"
        />
        @error('form.city_id')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-fwb.textarea
            wire:model="form.address"
            :label="__('app.address_text')"
            rows="4"
        />
        @error('form.address')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>
