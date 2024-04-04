<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Tests\Contracts\CreatesServiceables;
use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Tests\Traits\AssertsGatewayExists;
use Payavel\Orchestration\Tests\Traits\CreatesServices;
use PHPUnit\Framework\Attributes\Test;

abstract class TestOrchestrateServiceCommand extends TestCase implements CreatesServiceables
{
    use AssertsGatewayExists;
    use CreatesServices;

    #[Test]
    public function install_command_publishes_migration_and_generates_config_with_single_provider_and_merchant()
    {
        $service = $this->createService();
        $provider = $this->createProvider($service);
        $merchant = $this->createMerchant($service);

        $fakeGateway = $this->gatewayPath($service);
        $providerGateway = $this->gatewayPath($provider);

        $this->artisan('orchestrate:service')
            ->expectsQuestion('What should the service be named?', $service->getName())
            ->expectsQuestion('How should the ' . $service->getName() . ' service be identified?', $service->getId())
            ->expectsQuestion('Choose a driver to handle the ' . $service->getId() . ' service?', Config::get('orchestration.defaults.driver'))
            ->expectsQuestion('What should the ' . $service->getName() . ' provider be named?', $provider->getName())
            ->expectsQuestion('How should the ' . $provider->getName() . ' ' . $service->getName() . ' provider be identified?', $provider->getId())
            ->expectsConfirmation('Would you like to add another ' . $service->getName() . ' provider?', 'no')
            ->expectsQuestion('What should the ' . $service->getName() . ' merchant be named?', $merchant->getName())
            ->expectsQuestion('How should the ' . $merchant->getName() . ' ' . $service->getName() . ' merchant be identified?', $merchant->getId())
            ->expectsConfirmation('Would you like to add another ' . $service->getName() . ' merchant?', 'no')
            ->expectsOutputToContain('The ' . $service->getName() . ' config has been successfully generated.')
            ->expectsOutputToContain('Gateway [' . $fakeGateway->request . '] created successfully.')
            ->expectsOutputToContain('Gateway [' . $providerGateway->request . '] created successfully.')
            ->assertSuccessful();

        $configFile = Str::slug($service->getName()) . '.php';

        $this->assertFileExists(config_path($configFile));
        $config = require(config_path($configFile));

        $this->assertGatewayExists($service);
        $this->assertGatewayExists($provider);

        $this->assertEquals($provider->getId(), $config['defaults']['provider']);
        $this->assertEquals($merchant->getId(), $config['defaults']['merchant']);

        $this->makeSureProviderExists($service, $provider);
        $this->makeSureMerchantExists($service, $merchant);
        $this->makeSureProviderIsLinkedToMerchant($service, $provider, $merchant);

        $this->assertTrue(unlink(config_path($configFile)));
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

        $fakeGateway = $this->gatewayPath($service);
        $provider1Gateway = $this->gatewayPath($provider1);
        $provider2Gateway = $this->gatewayPath($provider2);

        $this->artisan('orchestrate:service')
            ->expectsQuestion('What should the service be named?', $service->getName())
            ->expectsQuestion('How should the ' . $service->getName() . ' service be identified?', $service->getId())
            ->expectsQuestion('Choose a driver to handle the ' . $service->getId() . ' service?', Config::get('orchestration.defaults.driver'))
            ->expectsQuestion('What should the ' . $service->getName() . ' provider be named?', $provider1->getName())
            ->expectsQuestion('How should the ' . $provider1->getName() . ' ' . $service->getName() . ' provider be identified?', $provider1->getId())
            ->expectsConfirmation('Would you like to add another ' . $service->getName() . ' provider?', 'yes')
            ->expectsQuestion('What should the ' . $service->getName() . ' provider be named?', $provider2->getName())
            ->expectsQuestion('How should the ' . $provider2->getName() . ' ' . $service->getName() . ' provider be identified?', $provider2->getId())
            ->expectsConfirmation('Would you like to add another ' . $service->getName() . ' provider?', 'no')
            ->expectsQuestion('Which provider will be used as default?', $provider1->getId())
            ->expectsQuestion('What should the ' . $service->getName() . ' merchant be named?', $merchant1->getName())
            ->expectsQuestion('How should the ' . $merchant1->getName() . ' ' . $service->getName() . ' merchant be identified?', $merchant1->getId())
            ->expectsQuestion("Which providers will the {$merchant1->getName()} merchant be integrating? (default first)", [$provider1->getId()])
            ->expectsConfirmation('Would you like to add another ' . $service->getName() . ' merchant?', 'yes')
            ->expectsQuestion('What should the ' . $service->getName() . ' merchant be named?', $merchant2->getName())
            ->expectsQuestion('How should the ' . $merchant2->getName() . ' ' . $service->getName() . ' merchant be identified?', $merchant2->getId())
            ->expectsQuestion("Which providers will the {$merchant2->getName()} merchant be integrating? (default first)", [$provider2->getId()])
            ->expectsConfirmation('Would you like to add another ' . $service->getName() . ' merchant?', 'yes')
            ->expectsQuestion('What should the ' . $service->getName() . ' merchant be named?', $merchant3->getName())
            ->expectsQuestion('How should the ' . $merchant3->getName() . ' ' . $service->getName() . ' merchant be identified?', $merchant3->getId())
            ->expectsQuestion("Which providers will the {$merchant3->getName()} merchant be integrating? (default first)", [$provider1->getId(), $provider2->getId()])
            ->expectsConfirmation('Would you like to add another ' . $service->getName() . ' merchant?', 'no')
            ->expectsQuestion("Which merchant will be used as default?", $merchant1->getId())
            ->expectsOutputToContain('The ' . $service->getName() . ' config has been successfully generated.')
            ->expectsOutputToContain('Gateway [' . $fakeGateway->request . '] created successfully.')
            ->expectsOutputToContain('Gateway [' . $provider1Gateway->request . '] created successfully.')
            ->expectsOutputToContain('Gateway [' . $provider2Gateway->request . '] created successfully.')
            ->assertSuccessful();

        $configFile = Str::slug($service->getName()) . '.php';

        $this->assertFileExists(config_path($configFile));
        $config = require(config_path($configFile));

        $randomProvider = $this->faker->randomElement([$provider1, $provider2]);
        $randomMerchant = $this->faker->randomElement([$merchant1, $merchant2, $merchant3]);

        $this->assertGatewayExists($service);
        $this->assertGatewayExists($randomProvider);

        $this->assertEquals($provider1->getId(), $config['defaults']['provider']);
        $this->assertEquals($merchant1->getId(), $config['defaults']['merchant']);

        $this->makeSureProviderExists($service, $randomProvider);
        $this->makeSureMerchantExists($service, $randomMerchant);
        $this->makeSureProviderIsLinkedToMerchant($service, $provider2, $merchant3);

        $this->assertTrue(unlink(config_path($configFile)));
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
