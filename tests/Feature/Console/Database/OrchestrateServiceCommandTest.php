<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Database;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Models\Account;
use Payavel\Orchestration\Models\Provider;
use Payavel\Orchestration\Tests\Feature\Console\TestOrchestrateServiceCommand;
use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;
use Payavel\Orchestration\Tests\Traits\SetsDatabaseDriver;

class OrchestrateServiceCommandTest extends TestOrchestrateServiceCommand
{
    use CreatesDatabaseServiceables;
    use SetsDatabaseDriver;

    /**
     * Determines if the generated migration has already been executed.
     *
     * @var boolean
     */
    private bool $migrated = false;

    protected function makeSureProviderExists(Serviceable $service, Providable $provider)
    {
        $this->migrate($service);

        $provider = Provider::find($provider->getId());

        $this->assertNotNull($provider);
        $this->assertEquals(
            'App\\Services\\'.Str::studly($service->getId()).'\\'.Str::studly($provider->getId()).Str::studly($service->getId()).'Request',
            $provider->gateway
        );
    }

    protected function makeSureAccountExists(Serviceable $service, Accountable $account)
    {
        $this->migrate($service);

        $account = Account::find($account->getId());

        $this->assertNotNull($account);
        $this->assertNotEmpty($account->providers);
    }

    protected function makeSureProviderIsLinkedToAccount(Serviceable $service, Providable $provider, Accountable $account)
    {
        $this->migrate($service);

        $provider = Provider::find($provider->getId());
        $account = Account::find($account->getId());

        $this->assertNotNull($provider->accounts()->where('accounts.id', $account->id)->first());
        $this->assertNotNull($account->providers()->where('providers.id', $provider->id)->first());
    }

    private function migrate(Serviceable $service)
    {
        if ($this->migrated) {
            return;
        }

        Account::where('service_id', $service->getId())->delete();
        Provider::where('service_id', $service->getId())->delete();

        $this->artisan('migrate');

        $this->migrated = true;
    }
}
