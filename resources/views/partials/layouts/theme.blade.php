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
    class="flex items-center gap-1 rounded-lg bg-gray-100 p-1 dark:bg-gray-700"
>
    <button type="button" @click="apply('light')" :class="theme === 'light' ? 'bg-white shadow dark:bg-gray-600' : ''" class="rounded-md p-1.5 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white" title="{{ __('general.light') }}">
        <x-lucide-sun class="h-4 w-4" />
    </button>
    <button type="button" @click="apply('dark')" :class="theme === 'dark' ? 'bg-white shadow dark:bg-gray-600' : ''" class="rounded-md p-1.5 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white" title="{{ __('general.dark') }}">
        <x-lucide-moon class="h-4 w-4" />
    </button>
    <button type="button" @click="apply('system')" :class="theme === 'system' ? 'bg-white shadow dark:bg-gray-600' : ''" class="rounded-md p-1.5 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white" title="{{ __('general.system') }}">
        <x-lucide-monitor class="h-4 w-4" />
    </button>
</div>
