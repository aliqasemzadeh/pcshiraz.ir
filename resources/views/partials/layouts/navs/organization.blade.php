<li>
    <a
        href="{{ route('panels.organization.dashboard.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.organization.dashboard.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.organization.dashboard.*'),
        ])
    >
        <x-lucide-layout-dashboard @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.organization.dashboard.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.organization.dashboard.*')]) />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.organization.dashboard.index') }}"
        wire:navigate
        class="group flex items-center rounded-lg p-2 text-sidebar-fg hover:bg-sidebar-hover hover:text-white"
    >
        <x-lucide-shopping-bag class="h-5 w-5 text-sidebar-fg group-hover:text-white" />
        <span class="ms-3">{{ __('general.orders') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.organization.dashboard.index') }}"
        wire:navigate
        class="group flex items-center rounded-lg p-2 text-sidebar-fg hover:bg-sidebar-hover hover:text-white"
    >
        <x-lucide-warehouse class="h-5 w-5 text-sidebar-fg group-hover:text-white" />
        <span class="ms-3">{{ __('general.inventory') }}</span>
    </a>
</li>
