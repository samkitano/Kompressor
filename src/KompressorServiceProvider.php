<?php

namespace samkitano\Kompressor;

use Illuminate\Support\ServiceProvider;

class KompressorServiceProvider extends ServiceProvider
{
    /**
     * Boot the service
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
            $this->registerFacades();
        }
    }

    /**
     * Register the service
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/kompressor.php', 'kompressor'
        );
    }

    /**
     * Register Publishable items
     */
    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__.'/../config/kompressor.php' => config_path('kompressor.php')
        ], 'kompressor');
    }

    /**
     * Register the Facade
     */
    protected function registerFacades()
    {
        $this->app->singleton('Kompressor', function ($app) {
            return new \samkitano\Kompressor\Kompressor();
        });
    }
}
