<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\DataTransferObjects\Account;
use Payavel\Orchestration\DataTransferObjects\Provider;
use Payavel\Orchestration\Support\ServiceConfig;

trait CreatesConfigServiceables
{
    /**
     * Creates a providable instance.
     *
     * @param Serviceable|null $service
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Providable
     */
    public function createProvider(Serviceable $service = null, $data = [])
    {
        if (is_null($service)) {
            $service = $this->createService();
        }

        $data['name'] = $data['name'] ?? Str::remove(['\'', ','], $this->faker->unique()->company());
        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower($data['name']));
        $data['gateway'] = $data['gateway'] ?? 'App\\Services\\'.Str::studly($service->getId()).'\\'.Str::studly($data['id']).Str::studly($service->getId()).'Request';

        ServiceConfig::set(
            $service,
            'providers.'.$data['id'],
            [
                'name' => $data['name'],
                'gateway' => $data['gateway']
            ]
        );

        return new Provider($service, $data);
    }

    /**
     * Creates a accountable instance.
     *
     * @param Serviceable|null $service
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Accountable
     */
    public function createAccount(Serviceable $service = null, $data = [])
    {
        if (is_null($service)) {
            $service = $this->createService();
        }

        $data['name'] = $data['name'] ?? Str::remove(['\'', ','], $this->faker->unique()->company());
        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower($data['name']));

        ServiceConfig::set($service, 'accounts.'.$data['id'], ['name' => $data['name']]);

        return new Account($service, $data);
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
            $account->getService(),
            'accounts.'.$account->getId().'.providers',
            array_merge(
                ServiceConfig::get($account->getService(), 'accounts.'.$account->getId().'.providers', []),
                [$provider->getId() => $data]
            )
        );
    }
}
