<li>
    <a
        href="{{ route('panels.sale.dashboard.index') }}"
        wire:navigate
        @class([
            'flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-brand',
            'bg-sidebar-active text-brand' => request()->routeIs('panels.sale.dashboard.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.dashboard.*'),
        ])
    >
        <x-lucide-layout-dashboard @class(['h-5 w-5', 'text-brand' => request()->routeIs('panels.sale.dashboard.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.dashboard.*')]) />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.brand.index') }}"
        wire:navigate
        @class([
            'flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-brand',
            'bg-sidebar-active text-brand' => request()->routeIs('panels.sale.catalog.brand.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.brand.*'),
        ])
    >
        <x-lucide-badge @class(['h-5 w-5', 'text-brand' => request()->routeIs('panels.sale.catalog.brand.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.brand.*')]) />
        <span class="ms-3">{{ __('general.brands') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.category.index') }}"
        wire:navigate
        @class([
            'flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-brand',
            'bg-sidebar-active text-brand' => request()->routeIs('panels.sale.catalog.category.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.category.*'),
        ])
    >
        <x-lucide-folders @class(['h-5 w-5', 'text-brand' => request()->routeIs('panels.sale.catalog.category.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.category.*')]) />
        <span class="ms-3">{{ __('general.categories') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.item.index') }}"
        wire:navigate
        @class([
            'flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-brand',
            'bg-sidebar-active text-brand' => request()->routeIs('panels.sale.catalog.item.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.item.*'),
        ])
    >
        <x-lucide-package @class(['h-5 w-5', 'text-brand' => request()->routeIs('panels.sale.catalog.item.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.item.*')]) />
        <span class="ms-3">{{ __('general.items') }}</span>
    </a>
</li>
