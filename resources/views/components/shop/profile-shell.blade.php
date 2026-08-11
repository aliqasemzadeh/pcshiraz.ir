@props([
    'title' => null,
])

@php
    $user = auth()->user();
    $navItems = [
        [
            'route' => 'profile',
            'label' => __('general.profile_settings'),
            'icon' => 'user',
            'active' => request()->routeIs('profile'),
            'guest' => true,
        ],
        [
            'route' => 'profile.addresses',
            'label' => __('app.my_addresses'),
            'icon' => 'map-pin',
            'active' => request()->routeIs('profile.addresses'),
            'guest' => false,
        ],
        [
            'route' => 'profile.orders',
            'label' => __('app.my_orders'),
            'icon' => 'shopping-bag',
            'active' => request()->routeIs('profile.orders*'),
            'guest' => false,
        ],
        [
            'route' => 'profile.cards',
            'label' => __('app.my_payment_cards'),
            'icon' => 'credit-card',
            'active' => request()->routeIs('profile.cards'),
            'guest' => false,
        ],
    ];

    $visibleNavItems = collect($navItems)->filter(fn (array $item) => $item['guest'] || $user !== null)->values();
    $currentLabel = $visibleNavItems->firstWhere('active')['label'] ?? ($title ?? __('general.profile'));
@endphp

<div class="gap-8 lg:flex">
    {{-- Desktop sidebar --}}
    <aside class="hidden h-fit w-72 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:block">
        @auth
            <div class="mb-3 flex items-center gap-3 rounded-lg bg-gray-50 p-2 dark:bg-gray-900/40">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-brand/10 text-brand">
                    <x-lucide-user class="h-5 w-5" />
                </div>
                <div class="min-w-0 text-start">
                    <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $user->full_name !== '' ? $user->full_name : __('general.profile') }}
                    </div>
                    @if ($user->mobile)
                        <div class="truncate text-xs text-gray-500 dark:text-gray-400" dir="ltr">{{ $user->mobile }}</div>
                    @endif
                </div>
            </div>
        @endauth

        <ul class="space-y-1">
            @foreach ($visibleNavItems as $item)
                <li>
                    <a
                        href="{{ route($item['route']) }}"
                        wire:navigate
                        @class([
                            'group flex items-center gap-3 rounded-lg p-2 text-sm font-medium transition',
                            'bg-brand/10 text-brand' => $item['active'],
                            'text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700' => ! $item['active'],
                        ])
                    >
                        @switch($item['icon'])
                            @case('user')
                                <x-lucide-user @class(['h-5 w-5 shrink-0', 'text-brand' => $item['active'], 'text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white' => ! $item['active']]) />
                                @break
                            @case('map-pin')
                                <x-lucide-map-pin @class(['h-5 w-5 shrink-0', 'text-brand' => $item['active'], 'text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white' => ! $item['active']]) />
                                @break
                            @case('shopping-bag')
                                <x-lucide-shopping-bag @class(['h-5 w-5 shrink-0', 'text-brand' => $item['active'], 'text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white' => ! $item['active']]) />
                                @break
                            @case('credit-card')
                                <x-lucide-credit-card @class(['h-5 w-5 shrink-0', 'text-brand' => $item['active'], 'text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white' => ! $item['active']]) />
                                @break
                        @endswitch
                        <span>{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @auth
            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="group flex w-full items-center gap-3 rounded-lg p-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-gray-700"
                    >
                        <x-lucide-log-out class="h-5 w-5 shrink-0" />
                        <span>{{ __('general.logout') }}</span>
                    </button>
                </form>
            </div>
        @endauth
    </aside>

    <div class="min-w-0 flex-1">
        {{-- Mobile mega-menu --}}
        <div class="relative mb-4 lg:hidden" x-data="{ open: false }" @keydown.escape.window="open = false">
            <button
                type="button"
                class="flex w-full items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                @click="open = ! open"
                :aria-expanded="open.toString()"
            >
                <span class="inline-flex items-center gap-2">
                    <x-lucide-menu class="h-4 w-4 text-brand" />
                    {{ $currentLabel }}
                </span>
                <x-lucide-chevron-down class="h-4 w-4 text-gray-400 transition" x-bind:class="open && 'rotate-180'" />
            </button>

            <div
                x-cloak
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                @click.outside="open = false"
                class="absolute inset-x-0 z-30 mt-2 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
            >
                @auth
                    <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $user->full_name !== '' ? $user->full_name : __('general.profile') }}
                        </div>
                        @if ($user->mobile)
                            <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" dir="ltr">{{ $user->mobile }}</div>
                        @endif
                    </div>
                @endauth

                <ul class="p-2">
                    @foreach ($visibleNavItems as $item)
                        <li>
                            <a
                                href="{{ route($item['route']) }}"
                                wire:navigate
                                @click="open = false"
                                @class([
                                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium',
                                    'bg-brand/10 text-brand' => $item['active'],
                                    'text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700' => ! $item['active'],
                                ])
                            >
                                @switch($item['icon'])
                                    @case('user')
                                        <x-lucide-user class="h-5 w-5 shrink-0" />
                                        @break
                                    @case('map-pin')
                                        <x-lucide-map-pin class="h-5 w-5 shrink-0" />
                                        @break
                                    @case('shopping-bag')
                                        <x-lucide-shopping-bag class="h-5 w-5 shrink-0" />
                                        @break
                                    @case('credit-card')
                                        <x-lucide-credit-card class="h-5 w-5 shrink-0" />
                                        @break
                                @endswitch
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                @auth
                    <div class="border-t border-gray-100 p-2 dark:border-gray-700">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-gray-700"
                            >
                                <x-lucide-log-out class="h-5 w-5 shrink-0" />
                                {{ __('general.logout') }}
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            @if ($title)
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">{{ $title }}</h1>
            @else
                <div></div>
            @endif

            @if (isset($actions))
                <div class="flex shrink-0 items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>

        {{ $slot }}
    </div>
</div>
