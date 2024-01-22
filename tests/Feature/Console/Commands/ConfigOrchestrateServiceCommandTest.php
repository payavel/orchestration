<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;
use Payavel\Orchestration\Tests\Traits\SetsConfigDriver;

class ConfigOrchestrateServiceCommandTest extends TestOrchestrateServiceCommand
{
    use CreatesConfigServiceables,
        SetsConfigDriver;

    protected function makeSureProviderExists(Serviceable $service, Providable $provider)
    {
        $config = require(config_path(Str::slug($service->getId()) . '.php'));

        $this->assertIsArray($config['providers']);
        $this->assertIsArray($config['providers'][$provider->getId()]);
        $this->assertEquals(
            'App\\Services\\' . Str::studly($service->getId()) . '\\' . Str::studly($provider->getId()) . Str::studly($service->getId()) . 'Request',
            $config['providers'][$provider->getId()]['gateway']
        );
    }

    protected function makeSureMerchantExists(Serviceable $service, Merchantable $merchant)
    {
        $config = require(config_path(Str::slug($service->getId()) . '.php'));

        $this->assertIsArray($config['merchants']);
        $this->assertIsArray($config['merchants'][$merchant->getId()]);
        $this->assertIsArray($config['merchants'][$merchant->getId()]['providers']);
        $this->assertNotEmpty($config['merchants'][$merchant->getId()]['providers']);
    }

    protected function makeSureProviderIsLinkedToMerchant(Serviceable $service, Providable $provider, Merchantable $merchant)
    {
        $config = require(config_path(Str::slug($service->getId()) . '.php'));

        $this->assertIsArray($config['merchants'][$merchant->getId()]['providers'][$provider->getId()]);
    }
}
