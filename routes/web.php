<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/login', 'pages::auth.login')->name('login');
Route::livewire('/', 'pages::shop.home.index')->name('home');
Route::livewire('/profile', 'pages::shop.profile.index')->name('profile');

Route::livewire('/category/{category:slug}', 'pages::shop.category.index')->name('shop.category');
Route::livewire('/category/{category:slug}/{brand:slug}', 'pages::shop.category.brand')->name('shop.category.brand');
Route::livewire('/items', 'pages::shop.item.index')->name('shop.items');
Route::livewire('/item/{item:slug}', 'pages::shop.item.view')->name('shop.item');
Route::livewire('/tag/{tag}', 'pages::shop.tag.index')->name('shop.tag');

Route::post('/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

Route::prefix('administrator')->name('panels.administrator.')->group(function () {
    Route::livewire('/dashboard', 'pages::panels.administrator.dashboard.index')->name('dashboard.index');
    Route::livewire('/users', 'pages::panels.administrator.user.index')->name('user.index');
    Route::livewire('/roles', 'pages::panels.administrator.role.index')->name('role.index');
    Route::livewire('/permissions', 'pages::panels.administrator.permission.index')->name('permission.index');
});

Route::prefix('sale')->name('panels.sale.')->group(function () {
    Route::livewire('/dashboard', 'pages::panels.sale.dashboard.index')->name('dashboard.index');
    Route::livewire('/catalog/brands', 'pages::panels.sale.catalog.brand.index')->name('catalog.brand.index');
    Route::livewire('/catalog/categories', 'pages::panels.sale.catalog.category.index')->name('catalog.category.index');
    Route::livewire('/catalog/items', 'pages::panels.sale.catalog.item.index')->name('catalog.item.index');
    Route::livewire('/catalog/items/{item}/prices', 'pages::panels.sale.catalog.item.price.index')->name('catalog.item.price.index');
});

Route::prefix('colleague')->name('panels.colleague.')->group(function () {
    Route::livewire('/dashboard', 'pages::panels.colleague.dashboard.index')->name('dashboard.index');
});

Route::prefix('organization')->name('panels.organization.')->group(function () {
    Route::livewire('/dashboard', 'pages::panels.organization.dashboard.index')->name('dashboard.index');
});
