<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Fluent\Account;
use Payavel\Orchestration\Fluent\Provider;
use Payavel\Orchestration\Fluent\ServiceConfig;
use RuntimeException;

trait CreatesConfigServiceables
{
    /**
     * Creates a providable instance.
     *
     * @param \Payavel\Orchestration\Fluent\ServiceConfig $serviceConfig
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function createProvider(ServiceConfig $serviceConfig, $data = [])
    {
        $data['name'] = $data['name'] ?? Str::remove(['\'', ','], $this->faker->unique()->company());
        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower($data['name']));
        $data['gateway'] = $data['gateway'] ?? 'App\\Services\\'.Str::studly($serviceConfig->id).'\\'.Str::studly($data['id']).Str::studly($serviceConfig->id).'Request';

        $serviceConfig->set(
            'providers.'.$data['id'],
            [
                'name' => $data['name'],
                'gateway' => $data['gateway']
            ]
        );

        return new Provider($serviceConfig, $data['id']);
    }

    /**
     * Creates a accountable instance.
     *
     * @param \Payavel\Orchestration\Fluent\ServiceConfig $serviceConfig
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function createAccount(ServiceConfig $serviceConfig, $data = [])
    {
        $data['name'] = $data['name'] ?? Str::remove(['\'', ','], $this->faker->unique()->company());
        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower($data['name']));

        $serviceConfig->set('accounts.'.$data['id'], ['name' => $data['name']]);

        return new Account($serviceConfig, $data['id']);
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
        if (!$account instanceof Account) {
            throw new RuntimeException();
        }

        $account->set('providers.'.$provider->getId(), $data);
    }
}
