@php($onBrand = $onBrand ?? false)
<div
    x-data="{
        theme: localStorage.getItem('color-theme') || 'system',
        apply(value) {
            this.theme = value;
            localStorage.setItem('color-theme', value);
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', value === 'dark' || (value === 'system' && prefersDark));
        }
    }"
    x-init="apply(theme)"
    @class([
        'flex items-center gap-0.5 rounded-lg p-0.5',
        'bg-white/20' => $onBrand,
        'bg-slate-200/80 dark:bg-gray-700' => ! $onBrand,
    ])
>
    <button
        type="button"
        @click="apply('light')"
        :class="theme === 'light' ? '{{ $onBrand ? 'bg-white/90 text-navbar-fg shadow' : 'bg-white shadow dark:bg-gray-600' }}' : ''"
        @class([
            'rounded-md p-1.5',
            'text-white/80 hover:text-white' => $onBrand,
            'text-gray-500 hover:text-navbar-fg dark:text-gray-400 dark:hover:text-white' => ! $onBrand,
        ])
        title="{{ __('general.light') }}"
    >
        <x-lucide-sun class="h-4 w-4" />
    </button>
    <button
        type="button"
        @click="apply('dark')"
        :class="theme === 'dark' ? '{{ $onBrand ? 'bg-white/90 text-navbar-fg shadow' : 'bg-white shadow dark:bg-gray-600' }}' : ''"
        @class([
            'rounded-md p-1.5',
            'text-white/80 hover:text-white' => $onBrand,
            'text-gray-500 hover:text-navbar-fg dark:text-gray-400 dark:hover:text-white' => ! $onBrand,
        ])
        title="{{ __('general.dark') }}"
    >
        <x-lucide-moon class="h-4 w-4" />
    </button>
    <button
        type="button"
        @click="apply('system')"
        :class="theme === 'system' ? '{{ $onBrand ? 'bg-white/90 text-navbar-fg shadow' : 'bg-white shadow dark:bg-gray-600' }}' : ''"
        @class([
            'rounded-md p-1.5',
            'text-white/80 hover:text-white' => $onBrand,
            'text-gray-500 hover:text-navbar-fg dark:text-gray-400 dark:hover:text-white' => ! $onBrand,
        ])
        title="{{ __('general.system') }}"
    >
        <x-lucide-monitor class="h-4 w-4" />
    </button>
</div>
