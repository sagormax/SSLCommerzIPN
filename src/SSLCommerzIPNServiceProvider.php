<?php

namespace SSLWIRELESS\SSLCommerzIPN;

use Illuminate\Support\ServiceProvider;
use SSLWIRELESS\SSLCommerzIPN\Controllers\PaymentValidation;

class SSLCommerzIPNServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        # Load Views
        $this->loadViewsFrom(__DIR__.'/Views', 'SSLCOMZIPN');

        # Publish Views
        $this->publishes([
            __DIR__.'/Views' => base_path('resources/views/vendor/SSLWIRELESS/SSLCommerzIPN'),
        ]);

        # Publish Migrations
        $this->publishes([
            __DIR__ . '/Migrations/' => base_path('database/migrations/')
        ]);

        # Publish Images
        $this->publishes([
            __DIR__ . '/images/' => base_path('public/vendor/SSLWIRELESS/SSLCommerzIPN/images')
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('payment-validator', function() {
            return new PaymentValidation;
        });
    }
}