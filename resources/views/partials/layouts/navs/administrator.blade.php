<li>
    <a
        href="{{ route('panels.administrator.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.administrator.dashboard.*') ? 'bg-sidebar-hover' : '' }}"
    >
        <x-lucide-layout-dashboard class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.user.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.administrator.user.*') ? 'bg-sidebar-hover' : '' }}"
    >
        <x-lucide-users class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.users') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.role.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.administrator.role.*') ? 'bg-sidebar-hover' : '' }}"
    >
        <x-lucide-shield class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.roles') }}</span>
    </a>
</li>
