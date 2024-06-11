<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Config;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Tests\Feature\Console\TestOrchestrateServiceCommand;
use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;
use Payavel\Orchestration\Tests\Traits\SetsConfigDriver;

class OrchestrateServiceCommandTest extends TestOrchestrateServiceCommand
{
    use CreatesConfigServiceables;
    use SetsConfigDriver;

    protected function makeSureProviderExists(Serviceable $service, Providable $provider)
    {
        $config = require(config_path(Str::slug($service->getId()).'.php'));

        $this->assertIsArray($config['providers']);
        $this->assertIsArray($config['providers'][$provider->getId()]);
        $this->assertEquals(
            'App\\Services\\'.Str::studly($service->getId()).'\\'.Str::studly($provider->getId()).Str::studly($service->getId()).'Request',
            $config['providers'][$provider->getId()]['gateway']
        );
    }

    protected function makeSureAccountExists(Serviceable $service, Accountable $account)
    {
        $config = require(config_path(Str::slug($service->getId()).'.php'));

        $this->assertIsArray($config['accounts']);
        $this->assertIsArray($config['accounts'][$account->getId()]);
        $this->assertIsArray($config['accounts'][$account->getId()]['providers']);
        $this->assertNotEmpty($config['accounts'][$account->getId()]['providers']);
    }

    protected function makeSureProviderIsLinkedToAccount(Serviceable $service, Providable $provider, Accountable $account)
    {
        $config = require(config_path(Str::slug($service->getId()).'.php'));

        $this->assertIsArray($config['accounts'][$account->getId()]['providers'][$provider->getId()]);
    }
}
