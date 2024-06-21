<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Config;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Fluent\FluentConfig;
use Payavel\Orchestration\Tests\Feature\Console\TestOrchestrateServiceCommand;
use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;
use Payavel\Orchestration\Tests\Traits\SetsConfigDriver;

class OrchestrateServiceCommandTest extends TestOrchestrateServiceCommand
{
    use CreatesConfigServiceables;
    use SetsConfigDriver;

    protected function makeSureProviderExists(FluentConfig $serviceConfig, Providable $provider)
    {
        $data = require(config_path(Str::slug($serviceConfig->id).'.php'));

        $this->assertIsArray($data['providers']);
        $this->assertIsArray($data['providers'][$provider->getId()]);
        $this->assertEquals(
            'App\\Services\\'.Str::studly($serviceConfig->id).'\\'.Str::studly($provider->getId()).Str::studly($serviceConfig->id).'Request',
            $data['providers'][$provider->getId()]['gateway']
        );
    }

    protected function makeSureAccountExists(FluentConfig $serviceConfig, Accountable $account)
    {
        $data = require(config_path(Str::slug($serviceConfig->id).'.php'));

        $this->assertIsArray($data['accounts']);
        $this->assertIsArray($data['accounts'][$account->getId()]);
        $this->assertIsArray($data['accounts'][$account->getId()]['providers']);
        $this->assertNotEmpty($data['accounts'][$account->getId()]['providers']);
    }

    protected function makeSureProviderIsLinkedToAccount(FluentConfig $serviceConfig, Providable $provider, Accountable $account)
    {
        $data = require(config_path(Str::slug($serviceConfig->id).'.php'));

        $this->assertIsArray($data['accounts'][$account->getId()]['providers'][$provider->getId()]);
    }
}
