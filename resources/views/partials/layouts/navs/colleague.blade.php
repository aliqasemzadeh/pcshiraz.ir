<li>
    <a
        href="{{ route('panels.colleague.dashboard.index') }}"
        wire:navigate
        @class([
            'flex items-center rounded-lg p-2 hover:bg-sidebar-hover hover:text-brand',
            'bg-sidebar-active text-brand' => request()->routeIs('panels.colleague.dashboard.*'),
            'text-sidebar-fg' => ! request()->routeIs('panels.colleague.dashboard.*'),
        ])
    >
        <x-lucide-layout-dashboard @class(['h-5 w-5', 'text-brand' => request()->routeIs('panels.colleague.dashboard.*'), 'text-sidebar-fg' => ! request()->routeIs('panels.colleague.dashboard.*')]) />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.colleague.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-sidebar-fg hover:bg-sidebar-hover hover:text-brand"
    >
        <x-lucide-list-todo class="h-5 w-5 text-sidebar-fg" />
        <span class="ms-3">{{ __('general.tasks') }}</span>
    </a>
</li>
