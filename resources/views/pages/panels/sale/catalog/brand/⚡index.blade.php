<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.panels')] class extends Component
{
    //
};
?>

<x-slot name="title">{{ __('general.brands') }} - {{ config('app.name') }}</x-slot>

<div>
    <x-fwb.breadcrumb class="mb-4">
        <x-fwb.breadcrumb.item home>{{ __('general.sale') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item>{{ __('general.brands') }}</x-fwb.breadcrumb.item>
    </x-fwb.breadcrumb>

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-heading">{{ __('general.brands') }}</h1>

            <button
                type="button"
                x-modal:open="{ modal: 'sale.catalog.brand.create' }"
                class="inline-flex items-center gap-2 rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-300 dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800"
            >
                <x-lucide-plus class="h-4 w-4" />
                {{ __('general.create_brand') }}
            </button>
        </div>

        <x-fwb.card>
            <livewire:sale.catalog.brand-table :key="'sale-catalog-brand-table'" />
        </x-fwb.card>
    </div>
</div>
