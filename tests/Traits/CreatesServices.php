<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Fluent\Config as FluentConfig;
use Payavel\Orchestration\Support\ServiceConfig;

trait CreatesServices
{
    /**
     * Creates a serviceable instance.
     *
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function createService($data = [])
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
     * Sets the default configuration for a serviceable instance.
     *
     * @param Serviceable $service
     * @param Accountable|null $account
     * @param Providable|null $provider
     * @return void
     */
    public function setDefaultsForService(Serviceable $service, Accountable $account = null, Providable $provider = null)
    {
        ServiceConfig::set(
            $service,
            'defaults.account',
            $account instanceof Accountable ? $account->getId() : $account
        );

        if (is_null($provider) && ! is_null($account)) {
            $provider = Collection::make(
                ServiceConfig::get($service, 'accounts.'.$account->getId().'.providers')
            )
                ->keys()
                ->first();
        }

        ServiceConfig::set(
            $service,
            'defaults.provider',
            $provider instanceof Providable ? $provider->getId() : $provider
        );
    }
}
