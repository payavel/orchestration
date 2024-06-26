<?php

namespace Payavel\Orchestration\Tests\Feature\Console;

use Payavel\Orchestration\Fluent\ServiceConfig;
use Payavel\Orchestration\Tests\Contracts\CreatesServiceables;
use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Tests\Traits\AssertsServiceExists;
use Payavel\Orchestration\Tests\Traits\CreatesServices;
use PHPUnit\Framework\Attributes\Test;

abstract class TestOrchestrateProviderCommand extends TestCase implements CreatesServiceables
{
    use AssertsServiceExists;
    use CreatesServices;

    #[Test]
    public function orchestrate_provider_command_will_prompt_for_missing_arguments()
    {
        $serviceConfig = $this->createServiceConfig();
        $provider = $this->createProvider($serviceConfig);

        $configs = ServiceConfig::all()->map(fn ($config) => $config->id);

        $gateway = $this->gatewayPath($serviceConfig, $provider);

        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('orchestrate:provider')
            ->expectsQuestion('Which service will the provider be offering?', $configs->search($serviceConfig->getId()))
            ->expectsQuestion("How should the {$serviceConfig->name} provider be named?", $provider->getName())
            ->expectsQuestion("How should the {$serviceConfig->name} provider be identified?", $provider->getId())
            ->expectsOutputToContain("Gateway [app{$ds}{$gateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$gateway->response}] created successfully.")
            ->assertSuccessful();

        $this->assertGatewayExists($serviceConfig, $provider);
    }

    #[Test]
    public function orchestrate_provider_command_completes_without_asking_questions_when_providing_the_arguments()
    {
        $serviceConfig = $this->createServiceConfig();
        $provider = $this->createProvider($serviceConfig);

        $gateway = $this->gatewayPath($serviceConfig, $provider);

        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('orchestrate:provider', [
            'provider' => $provider->getName(),
            '--id' => $provider->getId(),
            '--service' => $serviceConfig->id,
        ])
            ->expectsOutputToContain("Gateway [app{$ds}{$gateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$gateway->response}] created successfully.")
            ->assertSuccessful();

        $this->assertGatewayExists($serviceConfig, $provider);
    }

    #[Test]
    public function orchestrate_provider_command_with_fake_argument_generates_fake_gateway()
    {
        $serviceConfig = $this->createServiceConfig();

        $gateway = $this->gatewayPath($serviceConfig);

        $ds = DIRECTORY_SEPARATOR;
        $this->artisan('orchestrate:provider', [
            '--service' => $serviceConfig->id,
            '--fake' => true,
        ])
            ->expectsOutputToContain("Gateway [app{$ds}{$gateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app{$ds}{$gateway->response}] created successfully.")
            ->assertSuccessful();

        $this->assertGatewayExists($serviceConfig);
    }

    #[Test]
    public function orchestrate_provider_command_using_fake_service()
    {
        $this->createServiceConfig();

        $this->artisan('orchestrate:provider', [
            '--service' => 'fake',
        ])
            ->expectsOutputToContain('Service with id fake does not exist.')
            ->assertSuccessful();
    }

    #[Test]
    public function orchestrate_provider_command_when_no_services_exist()
    {
        $this->artisan('orchestrate:provider')
            ->expectsOutputToContain('Your must first set up a service! Please call the orchestrate:service artisan command.')
            ->assertSuccessful();
    }
}
