<?php

namespace Payavel\Serviceable;

use Illuminate\Support\ServiceProvider;

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
        //
    }

    protected function registerPublishableAssets()
    {
        $this->publishes([
            __DIR__ . '../database/migrations/0001_01_01_000001_create_base_serviceable_tables.php' => database_path('migrations/0001_01_01_000001_create_base_serviceable_tables.php'),
        ], ['payavel', 'payavel-serviceable', 'payavel-migrations']);
    }

    protected function registerCommands()
    {
    }

    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '../database/migrations');
    }
}
