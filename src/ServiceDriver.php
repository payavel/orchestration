<?php

namespace Payavel\Orchestration;

use Illuminate\Support\Collection;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Traits\ServesConfig;

abstract class ServiceDriver
{
    use ServesConfig;

    /**
     * The compatible service.
     *
     * @var \Payavel\Orchestration\Contracts\Serviceable
     */
    protected Serviceable $service;

    /**
     * Assigns the service to the driver.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable $service
     * @return void
     */
    public function __construct(Serviceable $service)
    {
        $this->service = $this->resolveService($service);
    }

    /**
     * Resolve the serviceable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable $service
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function resolveService(Serviceable $service)
    {
        return $service;
    }

    /**
     * Refresh the driver's properties if necessary.
     *
     * @return void
     */
    public function refresh()
    {
        //
    }

    /**
     * Resolve the providable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|string|int $provider
     * @return \Payavel\Orchestration\Contracts\Providable|null
     */
    abstract public function resolveProvider($provider);

    /**
     * Get the default providable identifier.
     *
     * @param \Payavel\Orchestration\Contracts\Merchantable|null $merchant
     * @return string|int
     */
    abstract public function getDefaultProvider(Merchantable $merchant = null);

    /**
     * Resolve the merchantable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Merchantable|string|int $merchant
     * @return \Payavel\Orchestration\Contracts\Merchantable|null
     */
    abstract public function resolveMerchant($merchant);

    /**
     * Get the default merchantable identifier.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|null $provider
     * @return string|int
     */
    abstract public function getDefaultMerchant(Providable $provider = null);

    /**
     * Resolve the gateway.
     *
     * @param \Payavel\Orchestration\Contracts\Providable $provider
     * @param \Payavel\Orchestration\Contracts\Merchantable $merchant
     * @return \Payavel\Orchestration\ServiceRequest
     */
    abstract public function resolveGateway($provider, $merchant);

    /**
     * Generate the service skeleton based on the current driver.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable $service
     * @param \Illuminate\Support\Collection $providers
     * @param \Illuminate\Support\Collection $merchants
     * @param array $config
     * @return void
     */
    public static function generateService(Serviceable $service, Collection $providers, Collection $merchants, array $config)
    {
        //
    }

    /**
     * Get a collection of existing serviceables.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract public static function services();
}
