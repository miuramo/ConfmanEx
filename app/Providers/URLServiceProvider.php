<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class URLServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
    }
}
