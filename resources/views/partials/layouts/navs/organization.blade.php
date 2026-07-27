<li>
    <a
        href="{{ route('panels.organization.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.organization.dashboard.*') ? 'bg-sidebar-hover' : '' }}"
    >
        <x-lucide-layout-dashboard class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.organization.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover"
    >
        <x-lucide-shopping-bag class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.orders') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.organization.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover"
    >
        <x-lucide-warehouse class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.inventory') }}</span>
    </a>
</li>
