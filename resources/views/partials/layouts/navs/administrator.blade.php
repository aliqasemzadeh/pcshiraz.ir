<li>
    <a
        href="{{ route('panels.administrator.dashboard.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.dashboard.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.dashboard.*'),
        ])
    >
        <x-lucide-layout-dashboard @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.dashboard.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.dashboard.*')]) />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.user.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.user.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.user.*'),
        ])
    >
        <x-lucide-users @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.user.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.user.*')]) />
        <span class="ms-3">{{ __('general.users') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.domain.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.domain.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.domain.*'),
        ])
    >
        <x-lucide-globe @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.domain.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.domain.*')]) />
        <span class="ms-3">{{ __('general.domains') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.customer.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.customer.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.customer.*'),
        ])
    >
        <x-lucide-user-round @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.customer.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.customer.*')]) />
        <span class="ms-3">{{ __('general.customers') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.role.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.role.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.role.*'),
        ])
    >
        <x-lucide-shield @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.role.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.role.*')]) />
        <span class="ms-3">{{ __('general.roles') }}</span>
    </a>
</li>
