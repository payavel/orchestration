<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
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
    public function install_command_publishes_migration_and_generates_config_with_single_provider_and_merchant()
    {
        $service = $this->createService();
        $provider = $this->createProvider($service);
        $merchant = $this->createMerchant($service);

        $serviceConfig = $this->configPath($service);
        $serviceContract = $this->contractPath($service);
        $fakeGateway = $this->gatewayPath($service);
        $providerGateway = $this->gatewayPath($provider);

        $this->artisan('orchestrate:service')
            ->expectsQuestion('How should the service be named?', $service->getName())
            ->expectsQuestion('How should the service be identified?', $service->getId())
            ->expectsQuestion("Choose a driver for the {$service->getName()} service.", Config::get('orchestration.defaults.driver'))
            ->expectsQuestion("How should the {$service->getName()} provider be named?", $provider->getName())
            ->expectsQuestion("How should the {$service->getName()} provider be identified?", $provider->getId())
            ->expectsConfirmation("Would you like to add another {$service->getName()} provider?", 'no')
            ->expectsQuestion("How should the {$service->getName()} merchant be named?", $merchant->getName())
            ->expectsQuestion("How should the {$service->getName()} merchant be identified?", $merchant->getId())
            ->expectsConfirmation("Would you like to add another {$service->getName()} merchant?", 'no')
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
        $this->assertEquals($merchant->getId(), $config['defaults']['merchant']);

        $this->makeSureProviderExists($service, $provider);
        $this->makeSureMerchantExists($service, $merchant);
        $this->makeSureProviderIsLinkedToMerchant($service, $provider, $merchant);

        $this->assertTrue(unlink(config_path($serviceConfig->service)));
    }

    #[Test]
    public function install_command_publishes_migration_and_generates_config_with_multiple_providers_and_merchants()
    {
        $service = $this->createService();

        $provider1 = $this->createProvider($service);
        $provider2 = $this->createProvider($service);

        $merchant1 = $this->createMerchant($service);
        $merchant2 = $this->createMerchant($service);
        $merchant3 = $this->createMerchant($service);

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
            ->expectsQuestion("How should the {$service->getName()} merchant be named?", $merchant1->getName())
            ->expectsQuestion("How should the {$service->getName()} merchant be identified?", $merchant1->getId())
            ->expectsQuestion("Choose one or more {$service->getName()} providers for the {$merchant1->getName()} merchant.", [$provider1->getId()])
            ->expectsConfirmation("Would you like to add another {$service->getName()} merchant?", 'yes')
            ->expectsQuestion("How should the {$service->getName()} merchant be named?", $merchant2->getName())
            ->expectsQuestion("How should the {$service->getName()} merchant be identified?", $merchant2->getId())
            ->expectsQuestion("Choose one or more {$service->getName()} providers for the {$merchant2->getName()} merchant.", [$provider2->getId()])
            ->expectsConfirmation("Would you like to add another {$service->getName()} merchant?", 'yes')
            ->expectsQuestion("How should the {$service->getName()} merchant be named?", $merchant3->getName())
            ->expectsQuestion("How should the {$service->getName()} merchant be identified?", $merchant3->getId())
            ->expectsQuestion("Choose one or more {$service->getName()} providers for the {$merchant3->getName()} merchant.", [$provider1->getId(), $provider2->getId()])
            ->expectsConfirmation("Would you like to add another {$service->getName()} merchant?", 'no')
            ->expectsQuestion("Which merchant will be used as default?", $merchant1->getId())
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
        $randomMerchant = $this->faker->randomElement([$merchant1, $merchant2, $merchant3]);

        $this->assertConfigExists($service);
        $this->assertContractExists($service);
        $this->assertGatewayExists($service);
        $this->assertGatewayExists($randomProvider);

        $this->assertEquals($provider1->getId(), $config['defaults']['provider']);
        $this->assertEquals($merchant1->getId(), $config['defaults']['merchant']);

        $this->makeSureProviderExists($service, $randomProvider);
        $this->makeSureMerchantExists($service, $randomMerchant);
        $this->makeSureProviderIsLinkedToMerchant($service, $provider2, $merchant3);

        $this->assertTrue(unlink(config_path($serviceConfig->service)));
    }

    protected function makeSureProviderExists(Serviceable $service, Providable $provider)
    {
        //
    }

    protected function makeSureMerchantExists(Serviceable $service, Merchantable $merchant)
    {
        //
    }

    protected function makeSureProviderIsLinkedToMerchant(Serviceable $service, Providable $provider, Merchantable $merchant)
    {
        //
    }
}
