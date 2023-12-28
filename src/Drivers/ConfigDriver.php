<?php

namespace Payavel\Orchestration\Drivers;

use Illuminate\Support\Facades\Config;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\DataTransferObjects\Merchant;
use Payavel\Orchestration\DataTransferObjects\Provider;
use Payavel\Orchestration\DataTransferObjects\Service;
use Payavel\Orchestration\ServiceDriver;

class ConfigDriver extends ServiceDriver
{
    /**
     * Collection of the service's providers.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $providers;

    /**
     * Collection of the service's merchants.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $merchants;


    /**
     * Collect the service's providers & merchants.
     */
    public function __construct(Serviceable $service)
    {
        parent::__construct($service);

        $this->providers = collect($this->config($this->service->getId(), 'providers'));
        $this->merchants = collect($this->config($this->service->getId(), 'merchants'));
    }

    /**
     * Resolve the serviceable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable $service
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function resolveService(Serviceable $service)
    {
        if (! $service instanceof Service) {
            $service = Service::fromServiceable($service);
        }

        return $service;
    }

    /**
     * Resolve the providable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|string $provider
     * @return \Payavel\Orchestration\Contracts\Providable|null
     */
    public function resolveProvider($provider)
    {
        if ($provider instanceof Provider) {
            return $provider;
        }

        if (is_null($attributes = $this->providers->get($provider))) {
            return null;
        }

        return new Provider(
            $this->service,
            array_merge(['id' => $provider], $attributes)
        );
    }

    /**
     * Get the default providable identifier.
     *
     * @param \Payavel\Orchestration\Contracts\Merchantable|null $merchant
     * @return string|int|\Payavel\Orchestration\Contracts\Providable
     */
    public function getDefaultProvider(Merchantable $merchant = null)
    {
        if (
            ! $merchant instanceof Merchant ||
            is_null($provider = $merchant->providers->first())
        ) {
            return $this->config($this->service->getId(), 'defaults.provider');
        }

        return $provider['id'];
    }

    /**
     * Resolve the merchantable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Merchantable|string $merchant
     * @return \Payavel\Orchestration\Contracts\Merchantable|null
     */
    public function resolveMerchant($merchant)
    {
        if ($merchant instanceof Merchant) {
            return $merchant;
        }

        if (is_null($attributes = $this->merchants->get($merchant))) {
            return null;
        }

        return new Merchant(
            $this->service,
            array_merge(['id' => $merchant], $attributes)
        );
    }

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
    public function check($provider, $merchant)
    {
        return $merchant->providers->contains('id', $provider->id);
    }

    /**
     * Resolve the gateway class.
     *
     * @param \Payavel\Orchestration\Contracts\Providable $provider
     * @return string
     */
    public function resolveGatewayClass($provider)
    {
        return $provider->gateway;
    }

    /**
     * Get a collection of existing serviceables.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function services()
    {
        return collect(Config::get('orchestration.services', []))->map(
            fn ($value, $key) => new Service(array_merge(['id' => $key], $value))
        )->values();
    }
}
