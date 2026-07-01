<flux:sidebar.nav>
    <flux:sidebar.item icon="layout-dashboard" href="{{ route('panels.administrator.dashboard.index') }}" :current="request()->routeIs('panels.administrator.dashboard.*')">
        {{ __('main.administrator') }}
    </flux:sidebar.item>
</flux:sidebar.nav>
