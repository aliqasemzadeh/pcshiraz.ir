<li>
    <a
        href="{{ route('panels.sale.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.sale.dashboard.*') ? 'bg-sidebar-hover' : '' }}"
    >
        <x-lucide-layout-dashboard class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.brand.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.sale.catalog.brand.*') ? 'bg-sidebar-hover' : '' }}"
    >
        <x-lucide-badge class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.brands') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.category.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.sale.catalog.category.*') ? 'bg-sidebar-hover' : '' }}"
    >
        <x-lucide-folders class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.categories') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.item.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.sale.catalog.item.*') ? 'bg-sidebar-hover' : '' }}"
    >
        <x-lucide-package class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.items') }}</span>
    </a>
</li>
