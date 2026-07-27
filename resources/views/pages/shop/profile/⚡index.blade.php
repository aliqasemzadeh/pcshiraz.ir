<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    //
};
?>

<div class="flex min-h-[60vh] flex-col">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('general.profile') }}</h1>

        @auth
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" dir="ltr">{{ auth()->user()->mobile }}</p>

            <form method="POST" action="{{ route('logout') }}" class="mt-6">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-800 focus:ring-4 focus:ring-red-300 focus:outline-none dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                    <x-lucide-log-out class="h-4 w-4" />
                    {{ __('general.logout') }}
                </button>
            </form>
        @else
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('general.login_or_register') }}</p>

            <a
                href="{{ route('login') }}"
                wire:navigate
                class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 focus:outline-none dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800"
            >
                <x-lucide-log-in class="h-4 w-4" />
                {{ __('general.login') }}
            </a>
        @endauth
    </div>

    <div class="mt-auto flex flex-col items-center gap-2 border-t border-gray-200 pt-6 dark:border-gray-700">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('general.theme') }}</p>
        @include('partials.layouts.theme')
    </div>
</div>
