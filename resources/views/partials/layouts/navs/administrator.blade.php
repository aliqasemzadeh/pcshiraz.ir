<li>
    <a
        href="{{ route('panels.administrator.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('panels.administrator.dashboard.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
    >
        <x-lucide-layout-dashboard class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.user.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('panels.administrator.user.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
    >
        <x-lucide-users class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.users') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.role.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('panels.administrator.role.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
    >
        <x-lucide-shield class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.roles') }}</span>
    </a>
</li>
