<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    //
};
?>

<div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('general.home') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ config('app.name') }}</p>
    </div>
</div>
