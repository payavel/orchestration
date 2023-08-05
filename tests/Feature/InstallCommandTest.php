<?php

namespace Payavel\Serviceable\Tests\Feature;

use Illuminate\Support\Str;
use Payavel\Serviceable\Models\Merchant;
use Payavel\Serviceable\Models\Provider;
use Payavel\Serviceable\Models\Service;
use Payavel\Serviceable\Tests\TestCase;
use Payavel\Serviceable\Tests\TestProvider;

class InstallCommandTest extends TestCase
{    
    /** @test */
    public function install_command_publishes_migration_and_generates_config_with_single_provider_and_merchant()
    {
        $service = Service::factory()->make();
        $lowerCaseService = Str::lower($service->name);

        $provider = Provider::factory()->make([
            'service_id' => $service->id,
        ]);

        $merchant = Merchant::factory()->make();

        $this->artisan('service:install')
            ->expectsQuestion('What service would you like to add?', $service->name)
            ->expectsQuestion('How would you like to identify the ' . $service->name . ' service?', $service->id)
            ->expectsQuestion('What ' . $lowerCaseService . ' provider would you like to add?', $provider->name)
            ->expectsQuestion('How would you like to identify the ' . $provider->name . ' ' . $lowerCaseService . ' provider?', $provider->id)
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' provider?', 'no')
            ->expectsQuestion('What ' . $lowerCaseService . ' merchant would you like to add?', $merchant->name)
            ->expectsQuestion('How would you like to identify the ' . $merchant->name . ' ' . $lowerCaseService . ' merchant?', $merchant->id)
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'no')
            ->expectsOutput('The ' . $lowerCaseService . ' config has been successfully generated.')
            ->expectsOutput('Fake ' . $lowerCaseService . ' gateway generated successfully!')
            ->expectsOutput($provider->name . ' ' . $lowerCaseService . ' gateway generated successfully!')
            ->assertExitCode(0);

        $configFile = Str::slug($service->name) . '.php';
        
        $this->assertFileExists(config_path($configFile));
        $config = require(config_path($configFile));

        $this->assertEquals($provider->id, $config['defaults']['provider']);
        $this->assertEquals($merchant->id, $config['defaults']['merchant']);
        $this->assertEquals($provider->name, $config['providers'][$provider->id]['name']);
        $this->assertEquals($merchant->name, $config['merchants'][$merchant->id]['name']);
        $this->assertNotNull($config['merchants'][$merchant->id]['providers'][$provider->id]);

        $this->assertTrue(unlink(config_path($configFile)));
    }

    /** @test */
    public function install_command_publishes_migration_and_generates_config_with_multiple_providers_and_merchants()
    {
        $service = Service::factory()->make();
        $lowerCaseService = Str::lower($service->name);

        $provider1 = Provider::factory()->make([
            'service_id' => $service->id,
        ]);
        $provider2 = Provider::factory()->make([
            'service_id' => $service->id,
        ]);

        $merchant1 = Merchant::factory()->make();
        $merchant2 = Merchant::factory()->make();
        $merchant3 = Merchant::factory()->make();

        $this->artisan('service:install')
            ->expectsQuestion('What service would you like to add?', $service->name)
            ->expectsQuestion('How would you like to identify the ' . $service->name . ' service?', $service->id)
            ->expectsQuestion('What ' . $lowerCaseService . ' provider would you like to add?', $provider1->name)
            ->expectsQuestion('How would you like to identify the ' . $provider1->name . ' ' . $lowerCaseService . ' provider?', $provider1->id)
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' provider?', 'yes')
            ->expectsQuestion('What ' . $lowerCaseService . ' provider would you like to add?', $provider2->name)
            ->expectsQuestion('How would you like to identify the ' . $provider2->name . ' ' . $lowerCaseService . ' provider?', $provider2->id)
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' provider?', 'no')
            ->expectsChoice(
                'Which provider will be used as default?',
                $provider1->id,
                [$provider1->id, $provider2->id]
            )
            ->expectsQuestion('What ' . $lowerCaseService . ' merchant would you like to add?', $merchant1->name)
            ->expectsQuestion('How would you like to identify the ' . $merchant1->name . ' ' . $lowerCaseService . ' merchant?', $merchant1->id)
            ->expectsChoice(
                "Which providers will the {$merchant1->name} merchant be integrating? (default first)",
                [$provider1->id],
                [$provider1->id, $provider2->id]
            )
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'yes')
            ->expectsQuestion('What ' . $lowerCaseService . ' merchant would you like to add?', $merchant2->name)
            ->expectsQuestion('How would you like to identify the ' . $merchant2->name . ' ' . $lowerCaseService . ' merchant?', $merchant2->id)
            ->expectsChoice(
                "Which providers will the {$merchant2->name} merchant be integrating? (default first)",
                [$provider2->id],
                [$provider1->id, $provider2->id]
            )
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'yes')
            ->expectsQuestion('What ' . $lowerCaseService . ' merchant would you like to add?', $merchant3->name)
            ->expectsQuestion('How would you like to identify the ' . $merchant3->name . ' ' . $lowerCaseService . ' merchant?', $merchant3->id)
            ->expectsChoice(
                "Which providers will the {$merchant3->name} merchant be integrating? (default first)",
                [$provider1->id, $provider2->id],
                [$provider1->id, $provider2->id]
            )
            ->expectsConfirmation('Would you like to add another ' . $lowerCaseService . ' merchant?', 'no')
            ->expectsChoice(
                "Which merchant will be used as default?",
                $merchant1->id,
                [$merchant1->id, $merchant2->id, $merchant3->id]
            )
            ->expectsOutput('The ' . $lowerCaseService . ' config has been successfully generated.')
            ->expectsOutput('Fake ' . $lowerCaseService . ' gateway generated successfully!')
            ->expectsOutput($provider1->name . ' ' . $lowerCaseService . ' gateway generated successfully!')
            ->expectsOutput($provider2->name . ' ' . $lowerCaseService . ' gateway generated successfully!')
            ->assertExitCode(0);

        $configFile = Str::slug($service->name) . '.php';

        $this->assertFileExists(config_path($configFile));
        $config = require(config_path($configFile));

        $this->assertEquals($provider1->id, $config['defaults']['provider']);
        $this->assertEquals($merchant1->id, $config['defaults']['merchant']);

        $randomProvider = $this->faker->randomElement([$provider1, $provider2]);
        $this->assertNotNull($config['providers'][$randomProvider->id]);
        $this->assertEquals($randomProvider->name, $config['providers'][$randomProvider->id]['name']);

        $randomMerchant = $this->faker->randomElement([$merchant1, $merchant2, $merchant3]);
        $this->assertNotNull($config['merchants'][$randomMerchant->id]);
        $this->assertEquals($randomMerchant->name, $config['merchants'][$randomMerchant->id]['name']);
        $this->assertNotEmpty($config['merchants'][$randomMerchant->id]['providers']);

        $this->assertTrue(unlink(config_path($configFile)));
    }
}
