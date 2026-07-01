<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('common.direction') }}">
@include('partials.layouts.head')
<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
<div class="flex min-h-screen">
    <div class="flex-1 flex justify-center items-center">
        <div class="w-80 max-w-80 space-y-6">
            <div class="flex justify-center opacity-50">
                {{ config('app.name') }}
            </div>
            {{ $slot }}
            @include('partials.layouts.theme')
        </div>
    </div>
    <div class="flex-1 p-4 max-lg:hidden">
        <div class="text-white relative rounded-lg h-full w-full bg-zinc-900 flex flex-col items-start justify-end p-16" style="background-image: url('{{ asset('images/cover.png') }}'); background-size: cover">

        </div>
    </div>
</div>

@include('partials.layouts.foot')
</body>
</html>
