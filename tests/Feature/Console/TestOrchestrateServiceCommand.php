<?php

namespace Payavel\Orchestration\Tests\Feature\Console;

use Illuminate\Support\Facades\Config;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\ServiceConfig;
use Payavel\Orchestration\Tests\Contracts\CreatesServiceables;
use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Tests\Traits\AssertsServiceExists;
use Payavel\Orchestration\Tests\Traits\CreatesServices;
use PHPUnit\Framework\Attributes\Test;

abstract class TestOrchestrateServiceCommand extends TestCase implements CreatesServiceables
{
    use AssertsServiceExists;
    use CreatesServices;

    #[Test]
    public function install_command_publishes_migration_and_generates_config_with_single_provider_and_account()
    {
        $serviceConfig = $this->createServiceConfig();
        $provider = $this->createProvider($serviceConfig);
        $account = $this->createAccount($serviceConfig);

        $serviceConfigPath = $this->configPath($serviceConfig);
        $serviceContractPath = $this->contractPath($serviceConfig);
        $fakeGatewayPath = $this->gatewayPath($serviceConfig);
        $providerGatewayPath = $this->gatewayPath($serviceConfig, $provider);

        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('orchestrate:service', [
            'service' => $serviceConfig->name,
            '--id' => $serviceConfig->id,
        ])
            ->expectsQuestion("Choose a driver for the {$serviceConfig->name} service.", Config::get('orchestration.defaults.driver'))
            ->expectsQuestion("How should the {$serviceConfig->name} provider be named?", $provider->getName())
            ->expectsQuestion("How should the {$serviceConfig->name} provider be identified?", $provider->getId())
            ->expectsConfirmation("Would you like to add another {$serviceConfig->name} provider?", 'no')
            ->expectsQuestion("How should the {$serviceConfig->name} account be named?", $account->getName())
            ->expectsQuestion("How should the {$serviceConfig->name} account be identified?", $account->getId())
            ->expectsConfirmation("Would you like to add another {$serviceConfig->name} account?", 'no')
            ->expectsOutputToContain("Config [config{$ds}{$serviceConfigPath->orchestration}] created successfully.")
            ->expectsOutputToContain("Config [config{$ds}{$serviceConfigPath->service}] created successfully.")
            ->expectsOutputToContain("Contract [app{$ds}{$serviceContractPath->requester}] created successfully.")
            ->expectsOutputToContain("Contract [app{$ds}{$serviceContractPath->responder}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$fakeGatewayPath->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$fakeGatewayPath->response}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$providerGatewayPath->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$providerGatewayPath->response}] created successfully.")
            ->assertSuccessful();

        $config = require(config_path($serviceConfigPath->service));

        $this->assertContractExists($serviceConfig);
        $this->assertGatewayExists($serviceConfig);
        $this->assertGatewayExists($serviceConfig, $provider);

        $this->assertEquals($provider->getId(), $config['defaults']['provider']);
        $this->assertEquals($account->getId(), $config['defaults']['account']);

        $this->makeSureProviderExists($serviceConfig, $provider);
        $this->makeSureAccountExists($serviceConfig, $account);
        $this->makeSureProviderIsLinkedToAccount($serviceConfig, $provider, $account);

        $this->assertTrue(unlink(config_path($serviceConfigPath->service)));
    }

    #[Test]
    public function install_command_publishes_migration_and_generates_config_with_multiple_providers_and_accounts()
    {
        $serviceConfig = $this->createServiceConfig();

        $provider1 = $this->createProvider($serviceConfig);
        $provider2 = $this->createProvider($serviceConfig);

        $account1 = $this->createAccount($serviceConfig);
        $account2 = $this->createAccount($serviceConfig);
        $account3 = $this->createAccount($serviceConfig);

        $serviceConfigPath = $this->configPath($serviceConfig);
        $serviceContractPath = $this->contractPath($serviceConfig);
        $fakeGatewayPath = $this->gatewayPath($serviceConfig);
        $provider1GatewayPath = $this->gatewayPath($serviceConfig, $provider1);
        $provider2GatewayPath = $this->gatewayPath($serviceConfig, $provider2);

        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('orchestrate:service')
            ->expectsQuestion('How should the service be named?', $serviceConfig->name)
            ->expectsQuestion('How should the service be identified?', $serviceConfig->id)
            ->expectsQuestion("Choose a driver for the {$serviceConfig->name} service.", Config::get('orchestration.defaults.driver'))
            ->expectsQuestion("How should the {$serviceConfig->name} provider be named?", $provider1->getName())
            ->expectsQuestion("How should the {$serviceConfig->name} provider be identified?", $provider1->getId())
            ->expectsConfirmation("Would you like to add another {$serviceConfig->name} provider?", 'yes')
            ->expectsQuestion("How should the {$serviceConfig->name} provider be named?", $provider2->getName())
            ->expectsQuestion("How should the {$serviceConfig->name} provider be identified?", $provider2->getId())
            ->expectsConfirmation("Would you like to add another {$serviceConfig->name} provider?", 'no')
            ->expectsQuestion("Choose a default provider for the {$serviceConfig->name} service.", $provider1->getId())
            ->expectsQuestion("How should the {$serviceConfig->name} account be named?", $account1->getName())
            ->expectsQuestion("How should the {$serviceConfig->name} account be identified?", $account1->getId())
            ->expectsQuestion("Choose one or more {$serviceConfig->name} providers for the {$account1->getName()} account.", [$provider1->getId()])
            ->expectsConfirmation("Would you like to add another {$serviceConfig->name} account?", 'yes')
            ->expectsQuestion("How should the {$serviceConfig->name} account be named?", $account2->getName())
            ->expectsQuestion("How should the {$serviceConfig->name} account be identified?", $account2->getId())
            ->expectsQuestion("Choose one or more {$serviceConfig->name} providers for the {$account2->getName()} account.", [$provider2->getId()])
            ->expectsConfirmation("Would you like to add another {$serviceConfig->name} account?", 'yes')
            ->expectsQuestion("How should the {$serviceConfig->name} account be named?", $account3->getName())
            ->expectsQuestion("How should the {$serviceConfig->name} account be identified?", $account3->getId())
            ->expectsQuestion("Choose one or more {$serviceConfig->name} providers for the {$account3->getName()} account.", [$provider1->getId(), $provider2->getId()])
            ->expectsConfirmation("Would you like to add another {$serviceConfig->name} account?", 'no')
            ->expectsQuestion("Which account will be used as default?", $account1->getId())
            ->expectsOutputToContain("Config [config{$ds}{$serviceConfigPath->orchestration}] created successfully.")
            ->expectsOutputToContain("Config [config{$ds}{$serviceConfigPath->service}] created successfully.")
            ->expectsOutputToContain("Contract [app{$ds}{$serviceContractPath->requester}] created successfully.")
            ->expectsOutputToContain("Contract [app{$ds}{$serviceContractPath->responder}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$fakeGatewayPath->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$fakeGatewayPath->response}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$provider1GatewayPath->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$provider1GatewayPath->response}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$provider2GatewayPath->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$provider2GatewayPath->response}] created successfully.")
            ->assertSuccessful();

        $config = require(config_path($serviceConfigPath->service));

        $randomProvider = $this->faker->randomElement([$provider1, $provider2]);
        $randomAccount = $this->faker->randomElement([$account1, $account2, $account3]);

        $this->assertConfigExists($serviceConfig);
        $this->assertContractExists($serviceConfig);
        $this->assertGatewayExists($serviceConfig);
        $this->assertGatewayExists($serviceConfig, $randomProvider);

        $this->assertEquals($provider1->getId(), $config['defaults']['provider']);
        $this->assertEquals($account1->getId(), $config['defaults']['account']);

        $this->makeSureProviderExists($serviceConfig, $randomProvider);
        $this->makeSureAccountExists($serviceConfig, $randomAccount);
        $this->makeSureProviderIsLinkedToAccount($serviceConfig, $provider2, $account3);

        $this->assertTrue(unlink(config_path($serviceConfigPath->service)));
    }

    protected function makeSureProviderExists(ServiceConfig $serviceConfig, Providable $provider)
    {
        //
    }

    protected function makeSureAccountExists(ServiceConfig $serviceConfig, Accountable $account)
    {
        //
    }

    protected function makeSureProviderIsLinkedToAccount(ServiceConfig $serviceConfig, Providable $provider, Accountable $account)
    {
        //
    }
}
