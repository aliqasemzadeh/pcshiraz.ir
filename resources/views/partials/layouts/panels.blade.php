<ul class="space-y-1 font-medium">
    <li>
        <a
            href="{{ route('panels.administrator.dashboard.index') }}"
            wire:navigate
            class="flex items-center rounded-lg p-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 {{ request()->routeIs('panels.administrator.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
        >
            <x-lucide-shield-check class="h-4 w-4" />
            <span class="ms-2">{{ __('general.administrator') }}</span>
        </a>
    </li>
    <li>
        <a
            href="{{ route('panels.sale.dashboard.index') }}"
            wire:navigate
            class="flex items-center rounded-lg p-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 {{ request()->routeIs('panels.sale.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
        >
            <x-lucide-handshake class="h-4 w-4" />
            <span class="ms-2">{{ __('general.sale') }}</span>
        </a>
    </li>
    <li>
        <a
            href="{{ route('panels.colleague.dashboard.index') }}"
            wire:navigate
            class="flex items-center rounded-lg p-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 {{ request()->routeIs('panels.colleague.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
        >
            <x-lucide-users-round class="h-4 w-4" />
            <span class="ms-2">{{ __('general.colleague') }}</span>
        </a>
    </li>
    <li>
        <a
            href="{{ route('panels.organization.dashboard.index') }}"
            wire:navigate
            class="flex items-center rounded-lg p-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 {{ request()->routeIs('panels.organization.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
        >
            <x-lucide-building-2 class="h-4 w-4" />
            <span class="ms-2">{{ __('general.organization') }}</span>
        </a>
    </li>
</ul>
