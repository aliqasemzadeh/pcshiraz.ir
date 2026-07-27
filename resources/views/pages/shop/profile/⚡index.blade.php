<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    //
};
?>

<div class="flex min-h-[60vh] flex-col pt-16">
    <div class="rounded-xl border border-nav-border bg-surface p-6 shadow-sm sm:p-8">
        <h1 class="text-2xl font-semibold text-ink">{{ __('general.profile') }}</h1>

        @auth
            <p class="mt-2 text-sm text-sidebar-fg" dir="ltr">{{ auth()->user()->mobile }}</p>

            <form method="POST" action="{{ route('logout') }}" class="mt-6">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-danger px-5 py-2.5 text-sm font-medium text-white hover:bg-danger/90 focus:ring-4 focus:ring-danger/30 focus:outline-none">
                    <x-lucide-log-out class="h-4 w-4" />
                    {{ __('general.logout') }}
                </button>
            </form>
        @else
            <p class="mt-2 text-sm text-sidebar-fg">{{ __('general.login_or_register') }}</p>

            <a
                href="{{ route('login') }}"
                wire:navigate
                class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-strong focus:ring-4 focus:ring-brand/30 focus:outline-none"
            >
                <x-lucide-log-in class="h-4 w-4" />
                {{ __('general.login') }}
            </a>
        @endauth
    </div>

    <div class="mt-auto flex flex-col items-center gap-2 border-t border-nav-border pt-6">
        <p class="text-xs font-medium text-sidebar-fg">{{ __('general.theme') }}</p>
        @include('partials.layouts.theme')
    </div>
</div>
