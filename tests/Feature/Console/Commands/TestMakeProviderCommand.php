<?php

namespace Payavel\Serviceable\Tests\Feature\Console\Commands;

use Illuminate\Support\Str;
use Payavel\Serviceable\Contracts\Providable;
use Payavel\Serviceable\Contracts\Serviceable;
use Payavel\Serviceable\Service;
use Payavel\Serviceable\Tests\TestCase;
use Payavel\Serviceable\Tests\Traits\CreateServiceables;

class TestMakeProviderCommand extends TestCase
{
    use CreateServiceables;

    /** @test */
    public function make_payment_provider_command_will_prompt_for_missing_arguments()
    {
        $service = $this->createService();
        $provider = $this->createProvider($service);

        $services = Service::all()->map(fn ($service) => $service->getId());

        $this->artisan('service:provider')
            ->expectsChoice('Which service will the provider be offering?', $services->search($provider->getService()->getId()), $services->all())
            ->expectsQuestion('What ' . Str::replace('_', ' ', $provider->getService()->getId()) . ' provider would you like to add?', $provider->getName())
            ->expectsQuestion('How would you like to identify the ' . $provider->getName() . ' ' . Str::replace('_', ' ', $service->getId()) . ' provider?', $provider->getId())
            ->expectsOutput($provider->getName() . ' ' . Str::replace('_', ' ', $service->getId()) . ' gateway generated successfully!')
            ->assertExitCode(0);

        $this->assertGatewayExists($provider);
    }

    /** @test */
    public function make_payment_provider_command_completes_without_asking_questions_when_providing_the_arguments()
    {
        $provider = $this->createProvider();

        $this->artisan('service:provider', [
            'provider' => $provider->getName(),
            '--service' => $provider->getService()->getId(),
            '--id' => $provider->getId(),
        ])
            ->expectsOutput($provider->getName() . ' ' . Str::replace('_', ' ', $provider->getService()->getId()) . ' gateway generated successfully!')
            ->assertExitCode(0);

        $this->assertGatewayExists($provider);
    }

    /** @test */
    public function make_payment_provider_command_with_fake_argument_generates_fake_gateway()
    {
        $service = $this->createService();

        $this->artisan('service:provider', [
            '--service' => $service->getId(),
            '--fake' => true,
        ])
            ->expectsOutput('Fake ' . Str::replace('_', ' ', $service->getId()) . ' gateway generated successfully!')
            ->assertExitCode(0);

        $this->assertGatewayExists($service);
    }

    /** @test */
    public function make_provider_command_using_fake_service()
    {
        $this->createService();

        $this->artisan('service:provider', [
            '--service' => 'fake',
        ])
            ->expectsOutput('Service with id fake does not exist.')
            ->assertExitCode(0);
    }

    /** @test */
    public function make_provider_command_when_no_services_exist()
    {
        $this->artisan('service:provider')
            ->expectsOutput('Your must first set up a service! Please call the service:install artisan command.')
            ->assertExitCode(0);
    }

    private function assertGatewayExists(Serviceable $serviceable)
    {
        if ($serviceable instanceof Providable) {
            $service = Str::studly($serviceable->getService()->getId());
            $provider = Str::studly($serviceable->getId());
        } else {
            $service = Str::studly($serviceable->getId());
            $provider = 'Fake';
        }

        $servicePath = app_path("Services/{$service}");

        $this->assertTrue(file_exists("{$servicePath}/{$provider}{$service}Request.php"));
        $this->assertTrue(file_exists("{$servicePath}/{$provider}{$service}Response.php"));
    }
}
