@php
    /** @var \App\Livewire\Forms\InstallmentPlanForm $form */
@endphp

<div>
    <x-fwb.input wire:model="form.title" :label="__('general.title')" type="text" />
    @error('form.title')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.select
        wire:model="form.organization_id"
        :label="__('general.organization_entity').' ('.__('general.optional').')'"
    >
        <option value="">{{ __('general.global_plan') }}</option>
        @foreach ($organizations as $organization)
            <option value="{{ $organization->id }}">{{ $organization->code }}</option>
        @endforeach
    </x-fwb.select>
    @error('form.organization_id')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <x-fwb.input wire:model="form.term_months" :label="__('general.term_months')" type="number" min="1" />
        @error('form.term_months')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-fwb.input wire:model="form.priority" :label="__('general.priority')" type="number" />
        @error('form.priority')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <x-fwb.input wire:model="form.down_payment_percent" :label="__('general.down_payment_percent')" type="number" step="0.01" min="0" />
        @error('form.down_payment_percent')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-fwb.input wire:model="form.monthly_interest_percent" :label="__('general.monthly_interest_percent')" type="number" step="0.0001" min="0" />
        @error('form.monthly_interest_percent')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <x-fwb.input wire:model="form.max_financiable_amount" :label="__('general.max_financiable_amount').' ('.price_unit_label().')'" type="number" step="0.01" min="0" />
    @error('form.max_financiable_amount')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.input wire:model="form.down_payment_required_above" :label="__('general.down_payment_required_above').' ('.price_unit_label().')'" type="number" step="0.01" min="0" />
    @error('form.down_payment_required_above')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-fwb.input wire:model="form.min_down_payment_percent" :label="__('general.min_down_payment_percent')" type="number" step="0.01" min="0" />
    @error('form.min_down_payment_percent')
        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <x-fwb.input wire:model="form.min_order_amount" :label="__('general.min_order_amount').' ('.price_unit_label().')'" type="number" step="0.01" min="0" />
        @error('form.min_order_amount')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <x-fwb.input wire:model="form.max_order_amount" :label="__('general.max_order_amount').' ('.price_unit_label().')'" type="number" step="0.01" min="0" />
        @error('form.max_order_amount')
            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <x-fwb.checkbox id="plan-active-{{ $checkboxId ?? 'default' }}" wire:model="form.is_active" :label="__('general.active')" />
</div>
