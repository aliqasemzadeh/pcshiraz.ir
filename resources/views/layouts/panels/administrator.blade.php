<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('main.direction') }}">
@include('partials.layouts.head')
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.nav>
        <flux:sidebar.item icon="layout-dashboard" href="{{ route('panels.administrator.dashboard.index') }}" wire:navigate>{{ __('main.dashboard') }}</flux:sidebar.item>
        <flux:sidebar.item icon="logs" href="{{ route('log-viewer.index') }}">{{ __('main.logs') }}</flux:sidebar.item>
    </flux:sidebar.nav>
    <flux:sidebar.spacer />
    @include('partials.layouts.panels')
    @include('partials.layouts.theme')
</flux:sidebar>
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
    <flux:spacer />
    <flux:dropdown position="top" alignt="start">
        <flux:profile avatar="" />
        <flux:menu>
        </flux:menu>
    </flux:dropdown>
</flux:header>
{{ $slot }}

@include('partials.layouts.foot')
</body>
</html>
