<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

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
     *
     * Here we are defining a custom rate limiter for requests,
     * which allows unlimited requests for admin users and limits
     * other users to 10 requests per minute.
     */
    public function boot(): void
    {
        RateLimiter::for('custom', function ($request) {
            // Check if the user has an admin role
            return $request->user()->role == 'admin'
                ? Limit::none() // No limit for admin users
                : Limit::perMinute(10); // Limit to 10 requests per minute for others
        });
    }
}
