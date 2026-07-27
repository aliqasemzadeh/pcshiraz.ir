<nav class="fixed top-0 start-0 z-40 w-full border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    <div class="mx-auto flex max-w-screen-xl items-center gap-2 px-3 py-2">
        <a href="{{ route('home') }}" wire:navigate class="shrink-0">
            <span class="text-lg font-semibold whitespace-nowrap text-gray-900 dark:text-white">{{ config('app.name') }}</span>
        </a>

        <div class="min-w-0 flex-1">
            @include('partials.layouts.app.search')
        </div>

        <div class="hidden shrink-0 sm:block">
            @include('partials.layouts.theme')
        </div>

        <a
            href="{{ route('home') }}"
            wire:navigate
            class="inline-flex shrink-0 items-center justify-center rounded-lg p-2.5 text-gray-600 hover:bg-gray-100 focus:ring-2 focus:ring-gray-200 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
            title="{{ __('general.cart') }}"
        >
            <x-lucide-shopping-cart class="h-5 w-5" />
            <span class="sr-only">{{ __('general.cart') }}</span>
        </a>

        @auth
            <a
                href="{{ route('profile') }}"
                wire:navigate
                class="hidden shrink-0 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 md:inline-flex dark:text-gray-300 dark:hover:bg-gray-700"
                title="{{ __('general.profile') }}"
            >
                <x-lucide-user class="h-5 w-5" />
                <span>{{ __('general.profile') }}</span>
            </a>
        @else
            <a
                href="{{ route('login') }}"
                wire:navigate
                class="hidden shrink-0 items-center justify-center gap-2 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 focus:outline-none md:inline-flex dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800"
            >
                <x-lucide-log-in class="h-4 w-4" />
                {{ __('general.login') }}
            </a>
        @endauth
    </div>
</nav>
