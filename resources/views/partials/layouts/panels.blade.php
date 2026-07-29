<ul class="space-y-1 font-medium">
    <li>
        <a
            href="{{ route('panels.administrator.dashboard.index') }}"
            wire:navigate
            @class([
                'group flex items-center rounded-lg p-2 text-sm hover:bg-sidebar-hover hover:text-white',
                'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.*'),
                'text-sidebar-fg' => ! request()->routeIs('panels.administrator.*'),
            ])
        >
            <x-lucide-shield-check @class(['h-4 w-4 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.*')]) />
            <span class="ms-2">{{ __('general.administrator') }}</span>
        </a>
    </li>
    <li>
        <a
            href="{{ route('panels.sale.dashboard.index') }}"
            wire:navigate
            @class([
                'group flex items-center rounded-lg p-2 text-sm hover:bg-sidebar-hover hover:text-white',
                'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.sale.*'),
                'text-sidebar-fg' => ! request()->routeIs('panels.sale.*'),
            ])
        >
            <x-lucide-handshake @class(['h-4 w-4 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.sale.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.*')]) />
            <span class="ms-2">{{ __('general.sale') }}</span>
        </a>
    </li>
    <li>
        <a
            href="{{ route('panels.colleague.dashboard.index') }}"
            wire:navigate
            @class([
                'group flex items-center rounded-lg p-2 text-sm hover:bg-sidebar-hover hover:text-white',
                'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.colleague.*'),
                'text-sidebar-fg' => ! request()->routeIs('panels.colleague.*'),
            ])
        >
            <x-lucide-users-round @class(['h-4 w-4 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.colleague.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.colleague.*')]) />
            <span class="ms-2">{{ __('general.colleague') }}</span>
        </a>
    </li>
    <li>
        <a
            href="{{ route('panels.organization.dashboard.index') }}"
            wire:navigate
            @class([
                'group flex items-center rounded-lg p-2 text-sm hover:bg-sidebar-hover hover:text-white',
                'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.organization.*'),
                'text-sidebar-fg' => ! request()->routeIs('panels.organization.*'),
            ])
        >
            <x-lucide-building-2 @class(['h-4 w-4 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.organization.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.organization.*')]) />
            <span class="ms-2">{{ __('general.organization') }}</span>
        </a>
    </li>
</ul>
