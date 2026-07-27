<ul class="space-y-1 font-medium">
    <li>
        <a
            href="{{ route('panels.administrator.dashboard.index') }}"
            wire:navigate
            class="flex items-center rounded-lg p-2 text-sm text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.administrator.*') ? 'bg-sidebar-hover' : '' }}"
        >
            <x-lucide-shield-check class="h-4 w-4 text-white" />
            <span class="ms-2">{{ __('general.administrator') }}</span>
        </a>
    </li>
    <li>
        <a
            href="{{ route('panels.sale.dashboard.index') }}"
            wire:navigate
            class="flex items-center rounded-lg p-2 text-sm text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.sale.*') ? 'bg-sidebar-hover' : '' }}"
        >
            <x-lucide-handshake class="h-4 w-4 text-white" />
            <span class="ms-2">{{ __('general.sale') }}</span>
        </a>
    </li>
    <li>
        <a
            href="{{ route('panels.colleague.dashboard.index') }}"
            wire:navigate
            class="flex items-center rounded-lg p-2 text-sm text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.colleague.*') ? 'bg-sidebar-hover' : '' }}"
        >
            <x-lucide-users-round class="h-4 w-4 text-white" />
            <span class="ms-2">{{ __('general.colleague') }}</span>
        </a>
    </li>
    <li>
        <a
            href="{{ route('panels.organization.dashboard.index') }}"
            wire:navigate
            class="flex items-center rounded-lg p-2 text-sm text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.organization.*') ? 'bg-sidebar-hover' : '' }}"
        >
            <x-lucide-building-2 class="h-4 w-4 text-white" />
            <span class="ms-2">{{ __('general.organization') }}</span>
        </a>
    </li>
</ul>
