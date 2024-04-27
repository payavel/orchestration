<?php

namespace Payavel\Orchestration;

use Illuminate\Support\ServiceProvider;
use Payavel\Orchestration\Console\Commands\OrchestrateService;
use Payavel\Orchestration\Console\Commands\OrchestrateProvider;
use Payavel\Orchestration\Console\Commands\OrchestrateStubs;

class OrchestrationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->registerPublishableAssets();

        $this->registerCommands();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/orchestration.php',
            'orchestration'
        );
    }

    protected function registerPublishableAssets()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/2024_01_01_000001_create_base_orchestration_tables.php' => database_path('migrations/2024_01_01_000001_create_base_orchestration_tables.php'),
        ], ['payavel', 'payavel-orchestration', 'payavel-migrations', 'payavel-orchestration-migrations']);
    }

    protected function registerCommands()
    {
        $this->commands([
            OrchestrateService::class,
            OrchestrateProvider::class,
            OrchestrateStubs::class,
        ]);
    }
}
