<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/login', 'pages::auth.login')->name('login');
Route::livewire('/', 'pages::shop.home.index')->name('home');

Route::post('/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

//Route::middleware('auth')->prefix('panels')->group(function () {
    Route::prefix('administrator')->name('panels.administrator.')->group(function () {
        Route::livewire('/dashboard', 'pages::panels.administrator.dashboard.index')->name('dashboard.index');
        Route::livewire('/users', 'pages::panels.administrator.user.index')->name('user.index');
        Route::livewire('/roles', 'pages::panels.administrator.role.index')->name('role.index');
        Route::livewire('/permissions', 'pages::panels.administrator.permission.index')->name('permission.index');
        Route::livewire('/domains', 'pages::panels.administrator.domain.index')->name('domain.index');
    });

    Route::prefix('sale')->name('panels.sale.')->group(function () {
        Route::livewire('/dashboard', 'pages::panels.sale.dashboard.index')->name('dashboard.index');
        Route::livewire('/catalog/brands', 'pages::panels.sale.catalog.brand.index')->name('catalog.brand.index');
        Route::livewire('/catalog/categories', 'pages::panels.sale.catalog.category.index')->name('catalog.category.index');
        Route::livewire('/catalog/items', 'pages::panels.sale.catalog.item.index')->name('catalog.item.index');
    });

    Route::prefix('colleague')->name('panels.colleague.')->group(function () {
        Route::livewire('/dashboard', 'pages::panels.colleague.dashboard.index')->name('dashboard.index');
    });

    Route::prefix('organization')->name('panels.organization.')->group(function () {
        Route::livewire('/dashboard', 'pages::panels.organization.dashboard.index')->name('dashboard.index');
    });
//});
