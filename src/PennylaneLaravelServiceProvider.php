<?php

namespace Ashraam\PennylaneLaravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Ashraam\PennylaneLaravel\PennylaneLaravel;
use Ashraam\PennylaneLaravel\Http\PennylaneClientFactory;

class PennylaneLaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'pennylane-laravel');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'pennylane-laravel');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('pennylane-laravel.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/pennylane-laravel'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/pennylane-laravel'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/pennylane-laravel'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'pennylane-laravel');

        $use2026ApiChanges = config('pennylane-laravel.use_2026_api_changes');
        $v2Headers = [
            "Authorization" => "Bearer ".config('pennylane-laravel.v2_key'),
        ];
        if ($use2026ApiChanges !== null && $use2026ApiChanges !== '') {
            $normalized2026Header = filter_var($use2026ApiChanges, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($normalized2026Header !== null) {
                $v2Headers['X-Use-2026-API-Changes'] = $normalized2026Header ? 'true' : 'false';
            }
        }

        $client_v1 = PennylaneClientFactory::createClient([
            'base_uri' => config('pennylane-laravel.endpoint'),
            'headers' => [
                "Authorization" => "Bearer ".config('pennylane-laravel.v1_key')
            ]
        ]);
        $client_v2 = PennylaneClientFactory::createClient([
            'base_uri' => config('pennylane-laravel.endpoint'),
            'headers' => $v2Headers,
        ], true, 'v2');

        // Register the main class to use with the facade
        $this->app->singleton(PennylaneLaravel::class, function (Application $app) use ($client_v1, $client_v2) {
            return new PennylaneLaravel($client_v1, $client_v2);
        });
    }
}
