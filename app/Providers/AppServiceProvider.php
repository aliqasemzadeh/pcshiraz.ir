<?php

namespace App\Providers;

use App\Notifications\Channels\TextMessageChannel;
use App\Services\Shop\CategoryMenuService;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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

        View::composer([
            'layouts.app',
            'layouts.panels',
            'layouts.auth',
            'partials.layouts.app.navbar',
            'partials.layouts.head',
        ], function ($view): void {
            $siteName = config('app.name');
            $siteLogoUrl = null;

            try {
                $settings = app(GeneralSettings::class);
                $siteName = $settings->site_name !== '' ? $settings->site_name : $siteName;

                if ($settings->logo_path) {
                    $siteLogoUrl = Storage::disk('public')->url($settings->logo_path);
                }
            } catch (\Throwable) {
                // Settings may not be migrated yet.
            }

            $view->with([
                'siteName' => $siteName,
                'siteLogoUrl' => $siteLogoUrl,
            ]);
        });
    }
}
