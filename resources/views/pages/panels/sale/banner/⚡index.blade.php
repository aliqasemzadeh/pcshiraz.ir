<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.panels')] class extends Component
{
    //
};
?>

<x-slot name="title">{{ __('general.banners') }} - {{ config('app.name') }}</x-slot>

<div>
    <x-fwb.breadcrumb class="mb-4">
        <x-fwb.breadcrumb.item home>{{ __('general.sale') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item>{{ __('general.banners') }}</x-fwb.breadcrumb.item>
    </x-fwb.breadcrumb>

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-heading">{{ __('general.banners') }}</h1>

            <x-ui.button
                type="button"
                color="green"
                :loading="false"
                x-modal:open="{ modal: 'sale.banner.create' }"
            >
                <x-slot:icon>
                    <x-lucide-plus class="h-4 w-4 me-2" />
                </x-slot:icon>
                {{ __('general.create_banner') }}
            </x-ui.button>
        </div>

        <x-fwb.card>
            <livewire:sale.banner-table :key="'sale-banner-table'" />
        </x-fwb.card>
    </div>
</div>
