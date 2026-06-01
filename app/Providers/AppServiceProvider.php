<?php

namespace App\Providers;

use App\Services\CarritoService;
use App\Support\SslCertificate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $sslOptions = SslCertificate::guzzleOptions();

        if ($sslOptions !== []) {
            Http::globalOptions($sslOptions);
        }

        View::composer(['cliente.partials.navbar', 'cliente.partials.evento-card'], function ($view) {
            $carritoUnidades = 0;
            if (auth()->check() && auth()->user()->isCliente()) {
                $carritoUnidades = app(CarritoService::class)->contarUnidades(auth()->user());
            }
            $view->with('carritoUnidades', $carritoUnidades);
        });
    }
}
