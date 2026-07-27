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
    class="flex items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 sm:gap-1 sm:p-1 dark:bg-gray-700"
>
    <button type="button" @click="apply('light')" :class="theme === 'light' ? 'bg-white shadow dark:bg-gray-600' : ''" class="rounded-md p-1 text-gray-500 hover:text-gray-900 sm:p-1.5 dark:text-gray-400 dark:hover:text-white" title="{{ __('general.light') }}">
        <x-lucide-sun class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
    </button>
    <button type="button" @click="apply('dark')" :class="theme === 'dark' ? 'bg-white shadow dark:bg-gray-600' : ''" class="rounded-md p-1 text-gray-500 hover:text-gray-900 sm:p-1.5 dark:text-gray-400 dark:hover:text-white" title="{{ __('general.dark') }}">
        <x-lucide-moon class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
    </button>
    <button type="button" @click="apply('system')" :class="theme === 'system' ? 'bg-white shadow dark:bg-gray-600' : ''" class="rounded-md p-1 text-gray-500 hover:text-gray-900 sm:p-1.5 dark:text-gray-400 dark:hover:text-white" title="{{ __('general.system') }}">
        <x-lucide-monitor class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
    </button>
</div>
