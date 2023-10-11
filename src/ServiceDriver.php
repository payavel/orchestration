<?php

namespace Payavel\Orchestration;

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
        $this->service = $service;
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
    public function getDefaultProvider(Merchantable $merchant = null)
    {
        return $this->config($this->service->getId(), 'defaults.provider');
    }

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
    public function getDefaultMerchant(Providable $provider = null)
    {
        return $this->config($this->service->getId(), 'defaults.merchant');
    }

    /**
     * Verify that the merchant is compatible with the provider.
     *
     * @param \Payavel\Orchestration\Contracts\Providable
     * @param \Payavel\Orchestration\Contracts\Merchantable
     * @return bool
     */
    abstract public function check($provider, $merchant);

    /**
     * Resolve the gateway class.
     *
     * @param \Payavel\Orchestration\Contracts\Providable $provider
     * @return string
     */
    abstract public function resolveGatewayClass($provider);

    /**
     * Get a collection of existing serviceables.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract public static function services();
}
