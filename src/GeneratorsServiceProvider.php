<?php

namespace Nissi\Generators;

use Illuminate\Support\ServiceProvider;

class GeneratorsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/stubs/' => resource_path('views/vendor/nissicreative/laravel-generators/stubs/'),
        ], 'stubs');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.nissicreative.generate-resource', function ($app) {
            return $app['Nissi\Generators\Commands\GenerateResource'];
        });

        $this->app->singleton('command.nissicreative.generate-fillable', function ($app) {
            return $app['Nissi\Generators\Commands\GenerateFillable'];
        });

        $this->app->singleton('command.nissicreative.generate-casts', function ($app) {
            return $app['Nissi\Generators\Commands\GenerateCasts'];
        });

        $this->app->singleton('command.nissicreative.generate-index-columns', function ($app) {
            return $app['Nissi\Generators\Commands\GenerateIndexColumns'];
        });

        $this->commands('command.nissicreative.generate-resource');
        $this->commands('command.nissicreative.generate-fillable');
        $this->commands('command.nissicreative.generate-casts');
        $this->commands('command.nissicreative.generate-index-columns');
    }
}
