<li>
    <a
        href="{{ route('panels.colleague.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover {{ request()->routeIs('panels.colleague.dashboard.*') ? 'bg-sidebar-hover' : '' }}"
    >
        <x-lucide-layout-dashboard class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.dashboard') }}</span>
    </a>
</li>
<li>
    <a
        href="{{ route('panels.colleague.dashboard.index') }}"
        wire:navigate
        class="flex items-center rounded-lg p-2 text-white hover:bg-sidebar-hover"
    >
        <x-lucide-list-todo class="h-5 w-5 text-white" />
        <span class="ms-3">{{ __('general.tasks') }}</span>
    </a>
</li>
