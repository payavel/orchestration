<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\DataTransferObjects\Merchant;
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

        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower(Str::remove(['\'', ','], $this->faker->unique()->company())));
        $data['gateway'] = $data['gateway'] ?? 'App\\Services\\' . Str::studly($service->getId()) . '\\' . Str::studly($data['id']) . Str::studly($service->getId()) . 'Request';

        ServiceConfig::set(
            $service,
            'providers.' . $data['id'],
            ['gateway' => $data['gateway']]
        );

        return new Provider($service, $data);
    }

    /**
     * Creates a merchantable instance.
     *
     * @param Serviceable|null $service
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Merchantable
     */
    public function createMerchant(Serviceable $service = null, $data = [])
    {
        if (is_null($service)) {
            $service = $this->createService();
        }

        $data['id'] = $data['id'] ?? preg_replace('/[^a-z0-9]+/i', '_', strtolower(Str::remove(['\'', ','], $this->faker->unique()->company())));

        ServiceConfig::set($service, 'merchants.' . $data['id'], []);

        return new Merchant($service, $data);
    }

    /**
     * Links a merchantable instance to a providable one.
     *
     * @param Merchantable $merchant
     * @param Providable $provider
     * @param array $data
     * @return void
     */
    public function linkMerchantToProvider(Merchantable $merchant, Providable $provider, $data = [])
    {
        ServiceConfig::set(
            $merchant->getService(),
            'merchants.' . $merchant->getId() . '.providers',
            array_merge(
                ServiceConfig::get($merchant->getService(), 'merchants.' . $merchant->getId() . '.providers', []),
                [$provider->getId() => $data]
            )
        );
    }
}
