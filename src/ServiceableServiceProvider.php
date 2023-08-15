<?php

namespace Payavel\Serviceable;

use Illuminate\Support\ServiceProvider;
use Payavel\Serviceable\Console\Commands\Install;
use Payavel\Serviceable\Console\Commands\MakeProvider;

class ServiceableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->registerPublishableAssets();

        $this->registerCommands();

        $this->registerMigrations();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/serviceable.php',
            'serviceable'
        );
    }

    protected function registerPublishableAssets()
    {
        $this->publishes([
            __DIR__ . '../database/migrations/0001_01_01_000001_create_base_serviceable_tables.php' => database_path('migrations/0001_01_01_000001_create_base_serviceable_tables.php'),
        ], ['payavel', 'payavel-serviceable', 'payavel-migrations']);
    }

    protected function registerCommands()
    {
        $this->commands([
            Install::class,
            MakeProvider::class,
        ]);
    }

    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
