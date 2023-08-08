<?php

namespace Payavel\Serviceable\Drivers;

use Payavel\Serviceable\Contracts\Merchantable;
use Payavel\Serviceable\Models\Merchant;
use Payavel\Serviceable\Models\Provider;
use Payavel\Serviceable\Models\Service;
use Payavel\Serviceable\ServiceDriver;

class DatabaseDriver extends ServiceDriver
{
    /**
     * Resolve the providable instance.
     *
     * @param \Payavel\Serviceable\Contracts\Providable|string $provider
     * @return \Payavel\Serviceable\Contracts\Providable|null
     */
    public function resolveProvider($provider)
    {
        if (! $provider instanceof Provider) {
            $serviceProvider = config('serviceable.models.' . Provider::class, Provider::class);

            $provider = $serviceProvider::find($provider);
        }

        if (is_null($provider) || (! $provider->exists)) {
            return null;
        }

        return $provider;
    }

    /**
     * Get the default providable identifier.
     *
     * @param \Payavel\Serviceable\Contracts\Merchantable|null $merchant
     * @return string|int
     */
    public function getDefaultProvider(Merchantable $merchant = null)
    {
        if (! $merchant instanceof Merchant || is_null($provider = $merchant->providers()->wherePivot('is_default', true)->first())) {
            return parent::getDefaultProvider();
        }

        return $provider;
    }

    /**
     * Resolve the merchantable instance.
     *
     * @param \Payavel\Serviceable\Contracts\Merchantable|string $merchant
     * @return \Payavel\Serviceable\Contracts\Merchantable|null
     */
    public function resolveMerchant($merchant)
    {
        if (! $merchant instanceof Merchant) {
            $serviceMerchant = config('serviceable.models.' . Merchant::class, Merchant::class);

            $merchant = $serviceMerchant::find($merchant);
        }

        if (is_null($merchant) || (! $merchant->exists)) {
            return null;
        }

        return $merchant;
    }

    /**
     * Verify that the merchant is compatible with the provider.
     *
     * @param \Payavel\Serviceable\Contracts\Providable
     * @param \Payavel\Serviceable\Contracts\Merchantable
     * @return bool
     */
    public function check($provider, $merchant)
    {
        if (! $merchant->providers->contains($provider)) {
            return false;
        }

        return true;
    }

    /**
     * Resolve the gateway class.
     *
     * @param \Payavel\Serviceable\Contracts\Providable $provider
     * @return string
     */
    public function resolveGatewayClass($provider)
    {
        return $provider->request_class;
    }

    /**
     * Get a collection of existing serviceables.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function services()
    {
        return Service::all();
    }
}
