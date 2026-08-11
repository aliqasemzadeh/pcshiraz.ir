<div class="space-y-4">
    <div>
        <x-fwb.input
            wire:model="form.title"
            :label="__('app.payment_card_title')"
            type="text"
            :placeholder="__('app.payment_card_title_placeholder')"
        />
        @error('form.title')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-fwb.input
            wire:model="form.holder_name"
            :label="__('app.card_holder_name')"
            type="text"
        />
        @error('form.holder_name')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-fwb.input
            wire:model="form.card_number"
            :label="__('app.card_number')"
            type="text"
            dir="ltr"
            inputmode="numeric"
            maxlength="19"
            placeholder="6037-****-****-****"
        />
        @error('form.card_number')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-fwb.input
            wire:model="form.bank_name"
            :label="__('app.bank_name')"
            type="text"
        />
        @error('form.bank_name')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-fwb.checkbox
            wire:model="form.is_default"
            :label="__('app.default_payment_card')"
        />
        @error('form.is_default')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>
