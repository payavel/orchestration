<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Fluent\FluentConfig;
use Payavel\Orchestration\Support\ServiceConfig;

trait CreatesServices
{
    /**
     * Creates a service config instance.
     *
     * @param array $data
     * @return \Payavel\Orchestration\Fluent\FluentConfig
     */
    public function createServiceConfig($data = [])
    {
        $data['name'] = $data['name'] ?? Str::ucfirst($this->faker->unique()->word());
        $data['id'] = $data['id'] ?? Str::slug($data['name'], '_');
        $data['test_gateway'] = $data['test_gateway'] ?? '\\App\\Services\\'.Str::studly($data['id']).'\\Fake'.Str::studly($data['id']).'Request';

        Config::set('orchestration.services.'.$data['id'], Str::slug($data['id']));

        ServiceConfig::set($data['id'], 'name', $data['name']);
        ServiceConfig::set($data['id'], 'test_gateway', $data['test_gateway']);

        return new FluentConfig($data);
    }

    /**
     * Sets the defaults for the service config.
     *
     * @param \Payavel\Orchestration\Fluent\FluentConfig $serviceConfig
     * @param Accountable|null $account
     * @param Providable|null $provider
     * @return void
     */
    public function setDefaultsForService(FluentConfig $serviceConfig, Accountable $account = null, Providable $provider = null)
    {
        ServiceConfig::set(
            $serviceConfig,
            'defaults.account',
            $account instanceof Accountable ? $account->getId() : $account
        );

        if (is_null($provider) && ! is_null($account)) {
            $provider = Collection::make(
                ServiceConfig::get($serviceConfig, 'accounts.'.$account->getId().'.providers')
            )
                ->keys()
                ->first();
        }

        ServiceConfig::set(
            $serviceConfig,
            'defaults.provider',
            $provider instanceof Providable ? $provider->getId() : $provider
        );
    }
}
