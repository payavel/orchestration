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

        $drivers = array_keys(Config::get('orchestration.drivers'));

        $this->artisan('orchestrate:service')
            ->expectsQuestion('What service would you like to add?', $service->getName())
            ->expectsQuestion('How would you like to identify the ' . $service->getName() . ' service?', $service->getId())
            ->expectsChoice('Which driver will handle the ' . $service->getName() . ' service?', Config::get('orchestration.defaults.driver'), $drivers)
            ->expectsQuestion('What ' . $lowerCaseService . ' provider would you like to add?', $provider->getName())
            ->expectsQuestion('How would you like to identify the ' . $provider->getName() . ' ' . $lowerCaseService . ' provider?', $provider->getId())
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' provider?', 'no')
            ->expectsQuestion('What ' . $lowerCaseService . ' merchant would you like to add?', $merchant->getName())
            ->expectsQuestion('How would you like to identify the ' . $merchant->getName() . ' ' . $lowerCaseService . ' merchant?', $merchant->getId())
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'no')
            ->expectsOutput('The ' . $lowerCaseService . ' config has been successfully generated.')
            ->expectsOutput('Fake ' . $lowerCaseService . ' gateway generated successfully!')
            ->expectsOutput($provider->getName() . ' ' . $lowerCaseService . ' gateway generated successfully!')
            ->assertExitCode(0);

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

        $drivers = array_keys(Config::get('orchestration.drivers'));

        $this->artisan('orchestrate:service')
            ->expectsQuestion('What service would you like to add?', $service->getName())
            ->expectsQuestion('How would you like to identify the ' . $service->getName() . ' service?', $service->getId())
            ->expectsChoice('Which driver will handle the ' . $service->getName() . ' service?', Config::get('orchestration.defaults.driver'), $drivers)
            ->expectsQuestion('What ' . $lowerCaseService . ' provider would you like to add?', $provider1->getName())
            ->expectsQuestion('How would you like to identify the ' . $provider1->getName() . ' ' . $lowerCaseService . ' provider?', $provider1->getId())
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' provider?', 'yes')
            ->expectsQuestion('What ' . $lowerCaseService . ' provider would you like to add?', $provider2->getName())
            ->expectsQuestion('How would you like to identify the ' . $provider2->getName() . ' ' . $lowerCaseService . ' provider?', $provider2->getId())
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' provider?', 'no')
            ->expectsChoice(
                'Which provider will be used as default?',
                $provider1->getId(),
                [$provider1->getId(), $provider2->getId()]
            )
            ->expectsQuestion('What ' . $lowerCaseService . ' merchant would you like to add?', $merchant1->getName())
            ->expectsQuestion('How would you like to identify the ' . $merchant1->getName() . ' ' . $lowerCaseService . ' merchant?', $merchant1->getId())
            ->expectsChoice(
                "Which providers will the {$merchant1->getName()} merchant be integrating? (default first)",
                [$provider1->getId()],
                [$provider1->getId(), $provider2->getId()]
            )
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'yes')
            ->expectsQuestion('What ' . $lowerCaseService . ' merchant would you like to add?', $merchant2->getName())
            ->expectsQuestion('How would you like to identify the ' . $merchant2->getName() . ' ' . $lowerCaseService . ' merchant?', $merchant2->getId())
            ->expectsChoice(
                "Which providers will the {$merchant2->getName()} merchant be integrating? (default first)",
                [$provider2->getId()],
                [$provider1->getId(), $provider2->getId()]
            )
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'yes')
            ->expectsQuestion('What ' . $lowerCaseService . ' merchant would you like to add?', $merchant3->getName())
            ->expectsQuestion('How would you like to identify the ' . $merchant3->getName() . ' ' . $lowerCaseService . ' merchant?', $merchant3->getId())
            ->expectsChoice(
                "Which providers will the {$merchant3->getName()} merchant be integrating? (default first)",
                [$provider1->getId(), $provider2->getId()],
                [$provider1->getId(), $provider2->getId()]
            )
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'no')
            ->expectsChoice(
                "Which merchant will be used as default?",
                $merchant1->getId(),
                [$merchant1->getId(), $merchant2->getId(), $merchant3->getId()]
            )
            ->expectsOutput('The ' . $lowerCaseService . ' config has been successfully generated.')
            ->expectsOutput('Fake ' . $lowerCaseService . ' gateway generated successfully!')
            ->expectsOutput($provider1->getName() . ' ' . $lowerCaseService . ' gateway generated successfully!')
            ->expectsOutput($provider2->getName() . ' ' . $lowerCaseService . ' gateway generated successfully!')
            ->assertExitCode(0);

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
