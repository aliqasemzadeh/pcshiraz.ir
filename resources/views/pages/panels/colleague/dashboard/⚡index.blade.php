<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.panels')] class extends Component
{
    //
};
?>

<div>
    <x-fwb.breadcrumb class="mb-4">
        <x-fwb.breadcrumb.item home>{{ __('general.colleague') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item>{{ __('general.dashboard') }}</x-fwb.breadcrumb.item>
    </x-fwb.breadcrumb>

    <h1 class="text-2xl font-semibold text-heading">{{ __('general.dashboard') }}</h1>
</div>
