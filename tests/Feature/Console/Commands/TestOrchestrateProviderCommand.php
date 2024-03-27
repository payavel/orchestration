<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Illuminate\Support\Str;
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

        $this->artisan('orchestrate:provider')
            ->expectsQuestion('Which service will the provider be offering?', $services->search($provider->getService()->getId()))
            ->expectsQuestion('What should the ' . Str::replace('_', ' ', $provider->getService()->getId()) . ' provider be named?', $provider->getName())
            ->expectsQuestion('How should the ' . $provider->getName() . ' ' . Str::replace('_', ' ', $service->getId()) . ' provider be identified?', $provider->getId())
            ->expectsOutputToContain($provider->getName() . ' ' . Str::replace('_', ' ', $service->getId()) . ' gateway generated successfully!')
            ->assertSuccessful();

        $this->assertGatewayExists($provider);
    }

    #[Test]
    public function orchestrate_provider_command_completes_without_asking_questions_when_providing_the_arguments()
    {
        $provider = $this->createProvider();

        $this->artisan('orchestrate:provider', [
            'provider' => $provider->getId(),
            '--service' => $provider->getService()->getId(),
        ])
            ->expectsOutputToContain($provider->getName() . ' ' . Str::replace('_', ' ', $provider->getService()->getId()) . ' gateway generated successfully!')
            ->assertSuccessful();

        $this->assertGatewayExists($provider);
    }

    #[Test]
    public function orchestrate_provider_command_with_fake_argument_generates_fake_gateway()
    {
        $service = $this->createService();

        $this->artisan('orchestrate:provider', [
            '--service' => $service->getId(),
            '--fake' => true,
        ])
            ->expectsOutputToContain('Fake ' . Str::replace('_', ' ', $service->getId()) . ' gateway generated successfully!')
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
