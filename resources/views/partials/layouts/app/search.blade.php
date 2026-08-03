<form class="w-full max-w-2xl" onsubmit="return false;">
    <div class="flex items-stretch">
        @include('partials.layouts.app.search-category-mega')

        <livewire:shop.navbar-search :key="'shop-navbar-search'" />
    </div>
</form>
