<li>
    <a
        href="{{ route('panels.sale.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('panels.sale.dashboard.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
    >
        <x-lucide-layout-dashboard class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.brand.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('panels.sale.catalog.brand.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
    >
        <x-lucide-badge class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.brands') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.category.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('panels.sale.catalog.category.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
    >
        <x-lucide-folders class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.categories') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.item.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('panels.sale.catalog.item.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}"
    >
        <x-lucide-package class="h-5 w-5 text-gray-500 dark:text-gray-400" />
        <span class="ms-3">{{ __('general.items') }}</span>
    </a>
</li>
