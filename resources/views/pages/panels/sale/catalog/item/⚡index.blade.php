<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.panels')] class extends Component
{
    //
};
?>

<x-slot name="title">{{ __('general.items') }} - {{ config('app.name') }}</x-slot>

<div>
    <x-fwb.breadcrumb class="mb-4">
        <x-fwb.breadcrumb.item home>{{ __('general.sale') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item>{{ __('general.items') }}</x-fwb.breadcrumb.item>
    </x-fwb.breadcrumb>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-heading">{{ __('general.items') }}</h1>

            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button
                    type="button"
                    color="teal"
                    :loading="false"
                    x-modal:open="{ modal: 'sale.catalog.item.import' }"
                >
                    <x-slot:icon>
                        <x-lucide-download class="h-4 w-4 me-2" />
                    </x-slot:icon>
                    {{ __('general.import_from_hamrahtel') }}
                </x-ui.button>

                <x-ui.button
                    type="button"
                    color="green"
                    :loading="false"
                    x-modal:open="{ modal: 'sale.catalog.item.create' }"
                >
                    <x-slot:icon>
                        <x-lucide-plus class="h-4 w-4 me-2" />
                    </x-slot:icon>
                    {{ __('general.create_item') }}
                </x-ui.button>
            </div>
        </div>

        <x-fwb.card>
            <livewire:sale.catalog.item-table :key="'sale-catalog-item-table'" />
        </x-fwb.card>
    </div>
</div>
