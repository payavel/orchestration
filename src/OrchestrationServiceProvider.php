<?php

namespace Payavel\Orchestration;

use Illuminate\Support\ServiceProvider;
use Payavel\Orchestration\Console\Commands\Install;
use Payavel\Orchestration\Console\Commands\MakeProvider;
use Payavel\Orchestration\Console\Commands\PublishStubs;

class OrchestrationServiceProvider extends ServiceProvider
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
            __DIR__ . '/../config/orchestration.php',
            'orchestration'
        );
    }

    protected function registerPublishableAssets()
    {
        $this->publishes([
            __DIR__ . '../database/migrations/0001_01_01_000001_create_base_orchestration_tables.php' => database_path('migrations/0001_01_01_000001_create_base_orchestration_tables.php'),
        ], ['payavel', 'payavel-orchestration', 'payavel-migrations']);
    }

    protected function registerCommands()
    {
        $this->commands([
            Install::class,
            MakeProvider::class,
            PublishStubs::class,
        ]);
    }

    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
