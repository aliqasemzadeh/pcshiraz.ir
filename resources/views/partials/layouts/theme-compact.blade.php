@php($onBrand = $onBrand ?? false)
<div
    x-data="{
        theme: localStorage.getItem('color-theme') || 'system',
        apply(value) {
            this.theme = value;
            localStorage.setItem('color-theme', value);
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', value === 'dark' || (value === 'system' && prefersDark));
        },
        cycle() {
            const next = { light: 'dark', dark: 'system', system: 'light' };
            this.apply(next[this.theme] || 'light');
        }
    }"
    x-init="apply(theme)"
>
    <button
        type="button"
        @click="cycle()"
        @class([
            'inline-flex items-center justify-center rounded-lg p-1.5',
            'bg-white/20 text-white/80 hover:text-white' => $onBrand,
            'bg-slate-200/80 text-gray-500 hover:text-navbar-fg dark:bg-gray-700 dark:text-gray-400 dark:hover:text-white' => ! $onBrand,
        ])
        :title="theme === 'light' ? '{{ __('general.light') }}' : (theme === 'dark' ? '{{ __('general.dark') }}' : '{{ __('general.system') }}')"
    >
        <x-lucide-sun class="h-4 w-4" x-show="theme === 'light'" x-cloak />
        <x-lucide-moon class="h-4 w-4" x-show="theme === 'dark'" x-cloak />
        <x-lucide-monitor class="h-4 w-4" x-show="theme === 'system'" x-cloak />
    </button>
</div>
