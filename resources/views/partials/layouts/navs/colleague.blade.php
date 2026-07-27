<li>
    <a
        href="{{ route('panels.colleague.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('panels.colleague.dashboard.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
    >
        <x-lucide-layout-dashboard class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.colleague.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
    >
        <x-lucide-list-todo class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.tasks') }}</span>
    </a>
</li>
