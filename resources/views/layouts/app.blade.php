<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('partials.layouts.head')
    <body>
        {{ $slot }}

        @include('partials.layouts.foot')
    </body>
</html>
