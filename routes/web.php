<?php

use Illuminate\Support\Facades\Route;


Route::livewire('/login', 'pages::auth.login')->name('login');
Route::livewire('/', 'pages::shop.home.index')->name('home');

Route::livewire('/panels/administrator/dashboard', 'pages::panels.administrator.dashboard.index')->name('panels.administrator.dashboard.index');
