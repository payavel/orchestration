<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Illuminate\Support\Facades\Config;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
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
        $service = $this->createService();
        $provider = $this->createProvider($service);
        $account = $this->createAccount($service);

        $serviceConfig = $this->configPath($service);
        $serviceContract = $this->contractPath($service);
        $fakeGateway = $this->gatewayPath($service);
        $providerGateway = $this->gatewayPath($provider);

        $this->artisan('orchestrate:service', [
            'service' => $service->getName(),
            '--id' => $service->getId(),
        ])
            ->expectsQuestion("Choose a driver for the {$service->getName()} service.", Config::get('orchestration.defaults.driver'))
            ->expectsQuestion("How should the {$service->getName()} provider be named?", $provider->getName())
            ->expectsQuestion("How should the {$service->getName()} provider be identified?", $provider->getId())
            ->expectsConfirmation("Would you like to add another {$service->getName()} provider?", 'no')
            ->expectsQuestion("How should the {$service->getName()} account be named?", $account->getName())
            ->expectsQuestion("How should the {$service->getName()} account be identified?", $account->getId())
            ->expectsConfirmation("Would you like to add another {$service->getName()} account?", 'no')
            ->expectsOutputToContain("Config [config/{$serviceConfig->orchestration}] created successfully.")
            ->expectsOutputToContain("Config [config/{$serviceConfig->service}] created successfully.")
            ->expectsOutputToContain("Contract [app/{$serviceContract->requester}] created successfully.")
            ->expectsOutputToContain("Contract [app/{$serviceContract->responder}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$fakeGateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$fakeGateway->response}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$providerGateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$providerGateway->response}] created successfully.")
            ->assertSuccessful();

        $config = require(config_path($serviceConfig->service));

        $this->assertContractExists($service);
        $this->assertGatewayExists($service);
        $this->assertGatewayExists($provider);

        $this->assertEquals($provider->getId(), $config['defaults']['provider']);
        $this->assertEquals($account->getId(), $config['defaults']['account']);

        $this->makeSureProviderExists($service, $provider);
        $this->makeSureAccountExists($service, $account);
        $this->makeSureProviderIsLinkedToAccount($service, $provider, $account);

        $this->assertTrue(unlink(config_path($serviceConfig->service)));
    }

    #[Test]
    public function install_command_publishes_migration_and_generates_config_with_multiple_providers_and_accounts()
    {
        $service = $this->createService();

        $provider1 = $this->createProvider($service);
        $provider2 = $this->createProvider($service);

        $account1 = $this->createAccount($service);
        $account2 = $this->createAccount($service);
        $account3 = $this->createAccount($service);

        $serviceConfig = $this->configPath($service);
        $serviceContract = $this->contractPath($service);
        $fakeGateway = $this->gatewayPath($service);
        $provider1Gateway = $this->gatewayPath($provider1);
        $provider2Gateway = $this->gatewayPath($provider2);

        $this->artisan('orchestrate:service')
            ->expectsQuestion('How should the service be named?', $service->getName())
            ->expectsQuestion('How should the service be identified?', $service->getId())
            ->expectsQuestion("Choose a driver for the {$service->getName()} service.", Config::get('orchestration.defaults.driver'))
            ->expectsQuestion("How should the {$service->getName()} provider be named?", $provider1->getName())
            ->expectsQuestion("How should the {$service->getName()} provider be identified?", $provider1->getId())
            ->expectsConfirmation("Would you like to add another {$service->getName()} provider?", 'yes')
            ->expectsQuestion("How should the {$service->getName()} provider be named?", $provider2->getName())
            ->expectsQuestion("How should the {$service->getName()} provider be identified?", $provider2->getId())
            ->expectsConfirmation("Would you like to add another {$service->getName()} provider?", 'no')
            ->expectsQuestion("Choose a default provider for the {$service->getName()} service.", $provider1->getId())
            ->expectsQuestion("How should the {$service->getName()} account be named?", $account1->getName())
            ->expectsQuestion("How should the {$service->getName()} account be identified?", $account1->getId())
            ->expectsQuestion("Choose one or more {$service->getName()} providers for the {$account1->getName()} account.", [$provider1->getId()])
            ->expectsConfirmation("Would you like to add another {$service->getName()} account?", 'yes')
            ->expectsQuestion("How should the {$service->getName()} account be named?", $account2->getName())
            ->expectsQuestion("How should the {$service->getName()} account be identified?", $account2->getId())
            ->expectsQuestion("Choose one or more {$service->getName()} providers for the {$account2->getName()} account.", [$provider2->getId()])
            ->expectsConfirmation("Would you like to add another {$service->getName()} account?", 'yes')
            ->expectsQuestion("How should the {$service->getName()} account be named?", $account3->getName())
            ->expectsQuestion("How should the {$service->getName()} account be identified?", $account3->getId())
            ->expectsQuestion("Choose one or more {$service->getName()} providers for the {$account3->getName()} account.", [$provider1->getId(), $provider2->getId()])
            ->expectsConfirmation("Would you like to add another {$service->getName()} account?", 'no')
            ->expectsQuestion("Which account will be used as default?", $account1->getId())
            ->expectsOutputToContain("Config [config/{$serviceConfig->orchestration}] created successfully.")
            ->expectsOutputToContain("Config [config/{$serviceConfig->service}] created successfully.")
            ->expectsOutputToContain("Contract [app/{$serviceContract->requester}] created successfully.")
            ->expectsOutputToContain("Contract [app/{$serviceContract->responder}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$fakeGateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$fakeGateway->response}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$provider1Gateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$provider1Gateway->response}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$provider2Gateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$provider2Gateway->response}] created successfully.")
            ->assertSuccessful();

        $config = require(config_path($serviceConfig->service));

        $randomProvider = $this->faker->randomElement([$provider1, $provider2]);
        $randomAccount = $this->faker->randomElement([$account1, $account2, $account3]);

        $this->assertConfigExists($service);
        $this->assertContractExists($service);
        $this->assertGatewayExists($service);
        $this->assertGatewayExists($randomProvider);

        $this->assertEquals($provider1->getId(), $config['defaults']['provider']);
        $this->assertEquals($account1->getId(), $config['defaults']['account']);

        $this->makeSureProviderExists($service, $randomProvider);
        $this->makeSureAccountExists($service, $randomAccount);
        $this->makeSureProviderIsLinkedToAccount($service, $provider2, $account3);

        $this->assertTrue(unlink(config_path($serviceConfig->service)));
    }

    protected function makeSureProviderExists(Serviceable $service, Providable $provider)
    {
        //
    }

    protected function makeSureAccountExists(Serviceable $service, Accountable $account)
    {
        //
    }

    protected function makeSureProviderIsLinkedToAccount(Serviceable $service, Providable $provider, Accountable $account)
    {
        //
    }
}
