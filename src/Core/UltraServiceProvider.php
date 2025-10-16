<?php

namespace LaravelUltra\Core;

use Illuminate\Support\ServiceProvider;
use LaravelUltra\Core\Console\InstallCommand;
use LaravelUltra\Core\Console\PublishCommand;

class UltraServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/ultra.php', 'ultra'
        );

        $this->app->singleton('ultra', function ($app) {
            return new Ultra($app);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                PublishCommand::class,
            ]);

            // Publish config
            $this->publishes([
                __DIR__.'/../../config/ultra.php' => config_path('ultra.php'),
            ], 'ultra-config');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ], 'ultra-migrations');

            // Publish views
            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/ultra'),
            ], 'ultra-views');

            // Publish assets
            $this->publishes([
                __DIR__.'/../../resources/js' => resource_path('js/vendor/ultra'),
                __DIR__.'/../../resources/css' => resource_path('css/vendor/ultra'),
            ], 'ultra-assets');
        }

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'ultra');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}