<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Fluent\FluentConfig;

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

        $serviceConfig = FluentConfig::find($data['id']);

        $serviceConfig->set('name', $data['name']);
        $serviceConfig->set('test_gateway', $data['test_gateway']);

        return $serviceConfig;
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
        $serviceConfig->set(
            'defaults.account',
            $account instanceof Accountable ? $account->getId() : $account
        );

        if (is_null($provider) && ! is_null($account)) {
            $provider = Collection::make(
                $serviceConfig->get('accounts.'.$account->getId().'.providers')
            )
                ->keys()
                ->first();
        }

        $serviceConfig->set(
            'defaults.provider',
            $provider instanceof Providable ? $provider->getId() : $provider
        );
    }
}
