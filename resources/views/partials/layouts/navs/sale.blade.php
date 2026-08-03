<li>
    <a
        href="{{ route('panels.sale.dashboard.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.sale.dashboard.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.dashboard.*'),
        ])
    >
        <x-lucide-layout-dashboard @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.sale.dashboard.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.dashboard.*')]) />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.order.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.sale.order.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.order.*'),
        ])
    >
        <x-lucide-shopping-bag @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.sale.order.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.order.*')]) />
        <span class="ms-3">{{ __('general.orders') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.installment-plan.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.sale.installment-plan.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.installment-plan.*'),
        ])
    >
        <x-lucide-calendar-range @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.sale.installment-plan.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.installment-plan.*')]) />
        <span class="ms-3">{{ __('general.installment_plans') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.brand.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.sale.catalog.brand.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.brand.*'),
        ])
    >
        <x-lucide-badge @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.sale.catalog.brand.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.brand.*')]) />
        <span class="ms-3">{{ __('general.brands') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.category.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.sale.catalog.category.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.category.*'),
        ])
    >
        <x-lucide-folders @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.sale.catalog.category.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.category.*')]) />
        <span class="ms-3">{{ __('general.categories') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.catalog.item.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.sale.catalog.item.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.item.*'),
        ])
    >
        <x-lucide-package @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.sale.catalog.item.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.catalog.item.*')]) />
        <span class="ms-3">{{ __('general.items') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.banner.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.sale.banner.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.banner.*'),
        ])
    >
        <x-lucide-image @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.sale.banner.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.banner.*')]) />
        <span class="ms-3">{{ __('general.banners') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.sale.article.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.sale.article.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.sale.article.*'),
        ])
    >
        <x-lucide-newspaper @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.sale.article.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.sale.article.*')]) />
        <span class="ms-3">{{ __('general.articles') }}</span>
    </a>
</li>
