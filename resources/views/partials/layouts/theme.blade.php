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
    class="flex shrink-0 items-center gap-0.5 rounded-lg bg-canvas p-0.5"
>
    <button
        type="button"
        @click="apply('light')"
        :class="theme === 'light' ? 'bg-surface text-ink shadow' : ''"
        class="shrink-0 rounded-md p-1.5 text-navbar-fg hover:text-ink"
        title="{{ __('general.light') }}"
    >
        <x-lucide-sun class="h-5 w-5 shrink-0" />
    </button>
    <button
        type="button"
        @click="apply('dark')"
        :class="theme === 'dark' ? 'bg-surface text-ink shadow' : ''"
        class="shrink-0 rounded-md p-1.5 text-navbar-fg hover:text-ink"
        title="{{ __('general.dark') }}"
    >
        <x-lucide-moon class="h-5 w-5 shrink-0" />
    </button>
    <button
        type="button"
        @click="apply('system')"
        :class="theme === 'system' ? 'bg-surface text-ink shadow' : ''"
        class="shrink-0 rounded-md p-1.5 text-navbar-fg hover:text-ink"
        title="{{ __('general.system') }}"
    >
        <x-lucide-monitor class="h-5 w-5 shrink-0" />
    </button>
</div>
