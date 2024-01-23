<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Models\Merchant;
use Payavel\Orchestration\Models\Provider;
use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;
use Payavel\Orchestration\Tests\Traits\SetsDatabaseDriver;

class DatabaseOrchestrateServiceCommandTest extends TestOrchestrateServiceCommand
{
    use CreatesDatabaseServiceables,
        SetsDatabaseDriver;

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
            'App\\Services\\' . Str::studly($service->getId()) . '\\' . Str::studly($provider->getId()) . Str::studly($service->getId()) . 'Request',
            $provider->gateway
        );
    }

    protected function makeSureMerchantExists(Serviceable $service, Merchantable $merchant)
    {
        $this->migrate($service);

        $merchant = Merchant::find($merchant->getId());

        $this->assertNotNull($merchant);
        $this->assertNotEmpty($merchant->providers);
    }

    protected function makeSureProviderIsLinkedToMerchant(Serviceable $service, Providable $provider, Merchantable $merchant)
    {
        $this->migrate($service);

        $provider = Provider::find($provider->getId());
        $merchant = Merchant::find($merchant->getId());
        
        $this->assertNotNull($provider->merchants()->where('merchants.id', $merchant->id)->first());
        $this->assertNotNull($merchant->providers()->where('providers.id', $provider->id)->first());
    }

    private function migrate(Serviceable $service)
    {
        if ($this->migrated) {
            return;
        }

        Merchant::where('service_id', $service->getId())->delete();
        Provider::where('service_id', $service->getId())->delete();

        $this->artisan('migrate');

        $this->migrated = true;
    }
}
