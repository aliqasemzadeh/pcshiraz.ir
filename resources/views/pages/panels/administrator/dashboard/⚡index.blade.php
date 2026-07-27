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
                <span class="text-sm font-medium text-ink">{{ __('general.administrator') }}</span>
            </li>
            <li>
                <div class="flex items-center">
                    <x-lucide-chevron-left class="mx-1 h-3 w-3 text-sidebar-fg rtl:rotate-180" />
                    <span class="ms-1 text-sm font-medium text-sidebar-fg md:ms-2">{{ __('general.dashboard') }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <h1 class="text-2xl font-semibold text-ink">{{ __('general.dashboard') }}</h1>
</div>
