<?php

namespace Payavel\Orchestration;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Payavel\Orchestration\Console\Commands\OrchestrateService;
use Payavel\Orchestration\Console\Commands\OrchestrateProvider;
use Payavel\Orchestration\Console\Commands\OrchestrateStubs;
use Payavel\Orchestration\Traits\MergesConfigRecursively;

class OrchestrationServiceProvider extends ServiceProvider
{
    use MergesConfigRecursively;

    /**
     * Boots the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->registerPublishableAssets();

        $this->registerCommands();
    }

    /**
     * Registers the service provider's dependencies.
     *
     * @return void
     */
    public function register(): void
    {
        $this->recursivelyMergeConfigFrom(
            __DIR__.'/../config/orchestration.php',
            'orchestration'
        );
    }

    /**
     * Registers the service provider's publishable assets.
     *
     * @return void
     */
    protected function registerPublishableAssets(): void
    {
        $this->publishes([
            __DIR__.'/../database/migrations/2024_01_01_000001_create_base_orchestration_tables.php' => database_path('migrations/2024_01_01_000001_create_base_orchestration_tables.php'),
        ], ['payavel', 'payavel-orchestration', 'payavel-migrations', 'payavel-orchestration-migrations']);
    }

    /**
     * Registers the service provider's commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands([
            OrchestrateService::class,
            OrchestrateProvider::class,
            OrchestrateStubs::class,
        ]);
    }
}
