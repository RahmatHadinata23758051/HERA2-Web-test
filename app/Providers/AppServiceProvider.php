<?php

namespace App\Providers;

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
        try {
            $settings = \App\Models\AppSetting::pluck('value', 'key')->toArray();
            \Illuminate\Support\Facades\View::share('app_settings', $settings);
        } catch (\Exception $e) {
            // Safe fallback during migrations
            \Illuminate\Support\Facades\View::share('app_settings', []);
        }
    }
}
