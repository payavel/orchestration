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
        $lowerCaseService = Str::lower($service->getName());

        $provider = $this->createProvider($service);

        $merchant = $this->createMerchant($service);

        $this->artisan('orchestrate:service')
            ->expectsQuestion('What should the service be named?', $service->getName())
            ->expectsQuestion('How should the ' . $service->getName() . ' service be identified?', $service->getId())
            ->expectsQuestion('Which driver will handle the ' . $service->getName() . ' service?', Config::get('orchestration.defaults.driver'))
            ->expectsQuestion('What should the ' . $lowerCaseService . ' provider be named?', $provider->getName())
            ->expectsQuestion('How should the ' . $provider->getName() . ' ' . $lowerCaseService . ' provider be identified?', $provider->getId())
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' provider?', 'no')
            ->expectsQuestion('What should the ' . $lowerCaseService . ' merchant be named?', $merchant->getName())
            ->expectsQuestion('How should the ' . $merchant->getName() . ' ' . $lowerCaseService . ' merchant be identified?', $merchant->getId())
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'no')
            ->expectsOutputToContain('The ' . $lowerCaseService . ' config has been successfully generated.')
            ->expectsOutputToContain('Fake ' . $lowerCaseService . ' gateway generated successfully!')
            ->expectsOutputToContain($provider->getName() . ' ' . $lowerCaseService . ' gateway generated successfully!')
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
        $lowerCaseService = Str::lower($service->getName());

        $provider1 = $this->createProvider($service);
        $provider2 = $this->createProvider($service);

        $merchant1 = $this->createMerchant($service);
        $merchant2 = $this->createMerchant($service);
        $merchant3 = $this->createMerchant($service);

        $this->artisan('orchestrate:service')
            ->expectsQuestion('What should the service be named?', $service->getName())
            ->expectsQuestion('How should the ' . $service->getName() . ' service be identified?', $service->getId())
            ->expectsQuestion('Which driver will handle the ' . $service->getName() . ' service?', Config::get('orchestration.defaults.driver'))
            ->expectsQuestion('What should the ' . $lowerCaseService . ' provider be named?', $provider1->getName())
            ->expectsQuestion('How should the ' . $provider1->getName() . ' ' . $lowerCaseService . ' provider be identified?', $provider1->getId())
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' provider?', 'yes')
            ->expectsQuestion('What should the ' . $lowerCaseService . ' provider be named?', $provider2->getName())
            ->expectsQuestion('How should the ' . $provider2->getName() . ' ' . $lowerCaseService . ' provider be identified?', $provider2->getId())
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' provider?', 'no')
            ->expectsQuestion('Which provider will be used as default?', $provider1->getId())
            ->expectsQuestion('What should the ' . $lowerCaseService . ' merchant be named?', $merchant1->getName())
            ->expectsQuestion('How should the ' . $merchant1->getName() . ' ' . $lowerCaseService . ' merchant be identified?', $merchant1->getId())
            ->expectsQuestion("Which providers will the {$merchant1->getName()} merchant be integrating? (default first)", [$provider1->getId()])
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'yes')
            ->expectsQuestion('What should the ' . $lowerCaseService . ' merchant be named?', $merchant2->getName())
            ->expectsQuestion('How should the ' . $merchant2->getName() . ' ' . $lowerCaseService . ' merchant be identified?', $merchant2->getId())
            ->expectsQuestion("Which providers will the {$merchant2->getName()} merchant be integrating? (default first)", [$provider2->getId()])
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'yes')
            ->expectsQuestion('What should the ' . $lowerCaseService . ' merchant be named?', $merchant3->getName())
            ->expectsQuestion('How should the ' . $merchant3->getName() . ' ' . $lowerCaseService . ' merchant be identified?', $merchant3->getId())
            ->expectsQuestion("Which providers will the {$merchant3->getName()} merchant be integrating? (default first)", [$provider1->getId(), $provider2->getId()])
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'no')
            ->expectsQuestion("Which merchant will be used as default?", $merchant1->getId())
            ->expectsOutputToContain('The ' . $lowerCaseService . ' config has been successfully generated.')
            ->expectsOutputToContain('Fake ' . $lowerCaseService . ' gateway generated successfully!')
            ->expectsOutputToContain($provider1->getName() . ' ' . $lowerCaseService . ' gateway generated successfully!')
            ->expectsOutputToContain($provider2->getName() . ' ' . $lowerCaseService . ' gateway generated successfully!')
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
