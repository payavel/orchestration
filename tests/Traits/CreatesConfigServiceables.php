<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\DataTransferObjects\Merchant;
use Payavel\Orchestration\DataTransferObjects\Provider;
use Payavel\Orchestration\DataTransferObjects\Service;

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

        Config::set(Str::slug($service->getId()) . '.providers.' . $data['id'], [
            'gateway' => $data['gateway'],
        ]);

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

        Config::set(Str::slug($service->getId()) . '.merchants.' . $data['id'], []);

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
        Config::set(
            $providers = Str::slug($merchant->getService()->getId()) . '.merchants.' . $merchant->getId() . '.providers',
            array_merge(
                Config::get($providers, []),
                [$provider->getId() => $data]
            )
        );
    }
}
