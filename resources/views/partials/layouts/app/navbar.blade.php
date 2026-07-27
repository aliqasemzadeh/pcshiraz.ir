<nav class="fixed top-0 start-0 z-40 w-full border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    <div class="mx-auto flex max-w-screen-xl flex-wrap items-center justify-between gap-3 p-4">
        <a href="{{ route('home') }}" wire:navigate class="flex items-center space-x-3 rtl:space-x-reverse">
            <span class="self-center text-xl font-semibold whitespace-nowrap dark:text-white">{{ config('app.name') }}</span>
        </a>

        <div class="order-3 w-full md:order-2 md:flex md:w-auto md:flex-1 md:justify-center">
            <div class="relative hidden w-full max-w-md md:block">
                <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
                    <x-lucide-search class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                </div>
                <input
                    type="search"
                    class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 ps-10 text-sm text-gray-900 focus:border-teal-500 focus:ring-teal-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                    placeholder="{{ __('general.search') }}"
                >
            </div>
        </div>

        <div class="order-2 flex items-center gap-2 md:order-3">
            <div class="hidden sm:block">
                @include('partials.layouts.theme')
            </div>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                        <x-lucide-log-out class="h-4 w-4" />
                        <span class="hidden sm:inline">{{ __('general.logout') }}</span>
                    </button>
                </form>
            @else
                <a
                    href="{{ route('login') }}"
                    wire:navigate
                    class="inline-flex items-center gap-2 rounded-lg bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 focus:outline-none dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800"
                >
                    <x-lucide-log-in class="h-4 w-4" />
                    {{ __('general.login') }}
                </a>
            @endauth
        </div>
    </div>
</nav>
