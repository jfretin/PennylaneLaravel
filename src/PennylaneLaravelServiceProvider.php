<?php

namespace Ashraam\PennylaneLaravel;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Ashraam\PennylaneLaravel\PennylaneLaravel;

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

        $client = new Client([
            'base_uri' => config('pennylane-laravel.endpoint'),
            'headers' => [
                "Authorization" => "Bearer ".config('pennylane-laravel.key')
            ]
        ]);

        // Register the main class to use with the facade
        $this->app->singleton(PennylaneLaravel::class, function (Application $app) use ($client) {
            return new PennylaneLaravel($client);
        });
    }
}
