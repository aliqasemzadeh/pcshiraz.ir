<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.panels')] class extends Component
{
    //
};
?>

<x-slot name="title">{{ __('general.orders') }} - {{ config('app.name') }}</x-slot>

<div>
    <x-fwb.breadcrumb class="mb-4">
        <x-fwb.breadcrumb.item home>{{ __('general.organization') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item>{{ __('general.orders') }}</x-fwb.breadcrumb.item>
    </x-fwb.breadcrumb>

    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-heading">{{ __('general.orders') }}</h1>
        </div>

        <x-fwb.card>
            <livewire:organization.order-table :key="'organization-order-table'" />
        </x-fwb.card>
    </div>
</div>
