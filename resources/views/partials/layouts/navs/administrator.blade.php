<li>
    <a
        href="{{ route('panels.administrator.dashboard.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.dashboard.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.dashboard.*'),
        ])
    >
        <x-lucide-layout-dashboard @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.dashboard.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.dashboard.*')]) />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.user.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.user.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.user.*'),
        ])
    >
        <x-lucide-users @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.user.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.user.*')]) />
        <span class="ms-3">{{ __('general.users') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.role.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.role.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.role.*'),
        ])
    >
        <x-lucide-shield @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.role.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.role.*')]) />
        <span class="ms-3">{{ __('general.roles') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.permission.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.permission.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.permission.*'),
        ])
    >
        <x-lucide-key-round @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.permission.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.permission.*')]) />
        <span class="ms-3">{{ __('general.permissions') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.banner.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.banner.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.banner.*'),
        ])
    >
        <x-lucide-image @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.banner.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.banner.*')]) />
        <span class="ms-3">{{ __('general.banners') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.article.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.article.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.article.*'),
        ])
    >
        <x-lucide-newspaper @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.article.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.article.*')]) />
        <span class="ms-3">{{ __('general.articles') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.setting.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.setting.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.setting.*'),
        ])
    >
        <x-lucide-settings @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.setting.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.setting.*')]) />
        <span class="ms-3">{{ __('general.settings') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.administrator.function.index') }}"
        wire:navigate
        @class([
            'group flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-white',
            'bg-sidebar-active text-sidebar-fg-active' => request()->routeIs('panels.administrator.function.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.administrator.function.*'),
        ])
    >
        <x-lucide-terminal @class(['h-5 w-5 group-hover:text-white', 'text-sidebar-fg-active' => request()->routeIs('panels.administrator.function.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.administrator.function.*')]) />
        <span class="ms-3">{{ __('general.functions') }}</span>
    </a>
</li>
