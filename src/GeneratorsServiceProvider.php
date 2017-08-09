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
        //
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

        $this->commands('command.nissicreative.generate-resource');
    }
}
