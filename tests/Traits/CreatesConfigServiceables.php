<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\DataTransferObjects\Account;
use Payavel\Orchestration\DataTransferObjects\Provider;
use Payavel\Orchestration\Fluent\FluentConfig;
use Payavel\Orchestration\Support\ServiceConfig;

trait CreatesConfigServiceables
{
    /**
     * Creates a providable instance.
     *
     * @param \Payavel\Orchestration\Fluent\FluentConfig|null $serviceConfig
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function createProvider(FluentConfig $serviceConfig = null, $data = [])
    {
        if (is_null($serviceConfig)) {
            $serviceConfig = $this->createServiceConfig();
        }

        $data['name'] = $data['name'] ?? Str::remove(['\'', ','], $this->faker->unique()->company());
        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower($data['name']));
        $data['gateway'] = $data['gateway'] ?? 'App\\Services\\'.Str::studly($serviceConfig->id).'\\'.Str::studly($data['id']).Str::studly($serviceConfig->id).'Request';

        ServiceConfig::set(
            $serviceConfig,
            'providers.'.$data['id'],
            [
                'name' => $data['name'],
                'gateway' => $data['gateway']
            ]
        );

        return new Provider($serviceConfig, $data);
    }

    /**
     * Creates a accountable instance.
     *
     * @param \Payavel\Orchestration\Fluent\FluentConfig|null $serviceConfig
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function createAccount(FluentConfig $serviceConfig = null, $data = [])
    {
        if (is_null($serviceConfig)) {
            $serviceConfig = $this->createServiceConfig();
        }

        $data['name'] = $data['name'] ?? Str::remove(['\'', ','], $this->faker->unique()->company());
        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower($data['name']));

        ServiceConfig::set($serviceConfig, 'accounts.'.$data['id'], ['name' => $data['name']]);

        return new Account($serviceConfig, $data);
    }

    /**
     * Links a accountable instance to a providable one.
     *
     * @param Accountable $account
     * @param Providable $provider
     * @param array $data
     * @return void
     */
    public function linkAccountToProvider(Accountable $account, Providable $provider, $data = [])
    {
        ServiceConfig::set(
            $account->getServiceConfig(),
            'accounts.'.$account->getId().'.providers',
            array_merge(
                ServiceConfig::get($account->getServiceConfig(), 'accounts.'.$account->getId().'.providers', []),
                [$provider->getId() => $data]
            )
        );
    }
}
