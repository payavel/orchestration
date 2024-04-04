<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Payavel\Orchestration\Service;
use Payavel\Orchestration\Tests\Contracts\CreatesServiceables;
use Payavel\Orchestration\Tests\TestCase;
use Payavel\Orchestration\Tests\Traits\AssertsGatewayExists;
use Payavel\Orchestration\Tests\Traits\CreatesServices;
use PHPUnit\Framework\Attributes\Test;

abstract class TestOrchestrateProviderCommand extends TestCase implements CreatesServiceables
{
    use AssertsGatewayExists;
    use CreatesServices;

    #[Test]
    public function orchestrate_provider_command_will_prompt_for_missing_arguments()
    {
        $service = $this->createService();
        $provider = $this->createProvider($service);

        $services = Service::all()->map(fn ($service) => $service->getId());

        $gateway = $this->gatewayPath($provider);

        $this->artisan('orchestrate:provider')
            ->expectsQuestion('Which service will the provider be offering?', $services->search($service->getId()))
            ->expectsQuestion("How should the {$service->getName()} provider be named?", $provider->getName())
            ->expectsQuestion("How should the {$service->getName()} provider be identified?", $provider->getId())
            ->expectsOutputToContain("Gateway [app/{$gateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$gateway->response}] created successfully.")
            ->assertSuccessful();

        $this->assertGatewayExists($provider);
    }

    #[Test]
    public function orchestrate_provider_command_completes_without_asking_questions_when_providing_the_arguments()
    {
        $provider = $this->createProvider();

        $gateway = $this->gatewayPath($provider);

        $this->artisan('orchestrate:provider', [
            'provider' => $provider->getId(),
            '--service' => $provider->getService()->getId(),
        ])
            ->expectsOutputToContain("Gateway [app/{$gateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$gateway->response}] created successfully.")
            ->assertSuccessful();

        $this->assertGatewayExists($provider);
    }

    #[Test]
    public function orchestrate_provider_command_with_fake_argument_generates_fake_gateway()
    {
        $service = $this->createService();

        $gateway = $this->gatewayPath($service);

        $this->artisan('orchestrate:provider', [
            '--service' => $service->getId(),
            '--fake' => true,
        ])
            ->expectsOutputToContain("Gateway [app/{$gateway->request}] created successfully.")
            ->expectsOutputToContain("Gateway [app/{$gateway->response}] created successfully.")
            ->assertSuccessful();

        $this->assertGatewayExists($service);
    }

    #[Test]
    public function orchestrate_provider_command_using_fake_service()
    {
        $this->createService();

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
