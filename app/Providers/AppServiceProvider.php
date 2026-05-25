<?php

namespace App\Providers;

use App\Support\SslCertificate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $sslOptions = SslCertificate::guzzleOptions();

        if ($sslOptions !== []) {
            Http::globalOptions($sslOptions);
        }
    }
}
