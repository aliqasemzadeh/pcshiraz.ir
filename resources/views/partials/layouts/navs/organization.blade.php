<li>
    <a
        href="{{ route('panels.organization.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('panels.organization.dashboard.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
    >
        <x-lucide-layout-dashboard class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.organization.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
    >
        <x-lucide-shopping-bag class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.orders') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.organization.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
    >
        <x-lucide-warehouse class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.inventory') }}</span>
    </a>
</li>
