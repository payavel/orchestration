<?php

namespace Payavel\Serviceable;

use Payavel\Serviceable\Contracts\Merchantable;
use Payavel\Serviceable\Contracts\Providable;
use Payavel\Serviceable\Contracts\Serviceable;
use Payavel\Serviceable\Traits\ServiceableConfig;

abstract class ServiceDriver
{
    use ServiceableConfig;

    /**
     * The compatible service.
     *
     * @var \Payavel\Serviceable\Contracts\Serviceable
     */
    protected Serviceable $service;

    /**
     * Assigns the service to the driver.
     *
     * @param \Payavel\Serviceable\Contracts\Serviceable $service
     * @return void
     */
    public function __construct(Serviceable $service)
    {
        $this->service = $service;
    }

    /**
     * Resolve the providable instance.
     *
     * @param \Payavel\Serviceable\Contracts\Providable|string|int $provider
     * @return \Payavel\Serviceable\Contracts\Providable|null
     */
    abstract public function resolveProvider($provider);

    /**
     * Get the default providable identifier.
     *
     * @param \Payavel\Serviceable\Contracts\Merchantable|null $merchant
     * @return string|int
     */
    public function getDefaultProvider(Merchantable $merchant = null)
    {
        return $this->config($this->service->getId(), 'defaults.provider');
    }

    /**
     * Resolve the merchantable instance.
     *
     * @param \Payavel\Serviceable\Contracts\Merchantable|string|int $merchant
     * @return \Payavel\Serviceable\Contracts\Merchantable|null
     */
    abstract public function resolveMerchant($merchant);

    /**
     * Get the default merchantable identifier.
     *
     * @param \Payavel\Serviceable\Contracts\Providable|null $provider
     * @return string|int
     */
    public function getDefaultMerchant(Providable $provider = null)
    {
        return $this->config($this->service->getId(), 'defaults.merchant');
    }

    /**
     * Verify that the merchant is compatible with the provider.
     *
     * @param \Payavel\Serviceable\Contracts\Providable
     * @param \Payavel\Serviceable\Contracts\Merchantable
     * @return bool
     */
    abstract public function check($provider, $merchant);

    /**
     * Resolve the gateway class.
     *
     * @param \Payavel\Serviceable\Contracts\Providable $provider
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
