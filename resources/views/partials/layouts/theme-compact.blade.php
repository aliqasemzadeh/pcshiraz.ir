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
    class="shrink-0"
>
    <button
        type="button"
        @click="cycle()"
        @class([
            'inline-flex shrink-0 items-center justify-center rounded-lg p-2.5',
            'bg-white/20 text-white/80 hover:text-white' => $onBrand,
            'text-navbar-fg hover:bg-slate-100 dark:text-gray-300 dark:hover:bg-gray-700' => ! $onBrand,
        ])
        :title="theme === 'light' ? '{{ __('general.light') }}' : (theme === 'dark' ? '{{ __('general.dark') }}' : '{{ __('general.system') }}')"
    >
        <span class="relative block h-5 w-5 shrink-0">
            <x-lucide-sun class="absolute inset-0 h-5 w-5" x-show="theme === 'light'" x-cloak />
            <x-lucide-moon class="absolute inset-0 h-5 w-5" x-show="theme === 'dark'" x-cloak />
            <x-lucide-monitor class="absolute inset-0 h-5 w-5" x-show="theme === 'system'" x-cloak />
        </span>
    </button>
</div>
