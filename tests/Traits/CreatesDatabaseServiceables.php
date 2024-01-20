<?php

namespace Payavel\Orchestration\Tests\Traits;

use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Models\Merchant;
use Payavel\Orchestration\Models\Provider;
use Payavel\Orchestration\Models\Service;

trait CreatesDatabaseServiceables
{
    /**
     * Creates a serviceable instance.
     *
     * @param array $data
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function createService($data = [])
    {
        return Service::factory()->create($data);
    }

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

        $data['service_id'] = $service->getId();

        return Provider::factory()->create($data);
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

        $data['service_id'] = $service->getId();

        return Merchant::factory()->create($data);
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
        throw_unless($merchant instanceof Merchant);

        $merchant->providers()->sync([$provider->getId() => $data], false);
    }

    /**
     * Sets the default configuration for a serviceable instance.
     *
     * @param Serviceable $service
     * @param Merchantable|null $merchant
     * @param Providable|null $provider
     * @return void
     */
    public function setDefaultsForService(Serviceable $service, Merchantable $merchant = null, Providable $provider = null)
    {
        throw_unless($service instanceof Service);

        if (is_null($provider) && ! is_null($merchant)) {
            $provider = $merchant->default_provider_id;
        }

        $service->update([
            'default_merchant_id' => $merchant instanceof Merchantable ? $merchant->getId() : $merchant,
            'default_provider_id' => $provider instanceof  Providable ? $provider->getId() : $provider,
        ]);
    }
}
