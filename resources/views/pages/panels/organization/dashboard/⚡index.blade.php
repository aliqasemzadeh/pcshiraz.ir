<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.panels')] class extends Component
{
    //
};
?>

<div>
    <nav class="mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
            <li class="inline-flex items-center">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('general.organization') }}</span>
            </li>
            <li>
                <div class="flex items-center">
                    <x-lucide-chevron-left class="mx-1 h-3 w-3 text-gray-400 rtl:rotate-180" />
                    <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2 dark:text-gray-400">{{ __('general.dashboard') }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('general.dashboard') }}</h1>
</div>
