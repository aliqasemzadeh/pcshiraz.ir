<?php

namespace App\Providers;

use App\Notifications\Channels\TextMessageChannel;
use App\Services\Shop\CategoryMenuService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Notification::extend('text-message', function ($app) {
            return $app->make(TextMessageChannel::class);
        });

        View::composer(['layouts.app', 'partials.layouts.app.*'], function ($view): void {
            $view->with(
                'shopCategoryMenu',
                app(CategoryMenuService::class)->get()
            );
        });
    }
}
