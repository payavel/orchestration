<?php

namespace Payavel\Orchestration\Drivers;

use Exception;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Models\Merchant;
use Payavel\Orchestration\Models\Provider;
use Payavel\Orchestration\Models\Service;
use Payavel\Orchestration\ServiceDriver;
use Payavel\Orchestration\Traits\GeneratesFiles;

class DatabaseDriver extends ServiceDriver
{
    use GeneratesFiles;

    /**
     * Resolve the serviceable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable $service
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function resolveService(Serviceable $service)
    {
        if (! $service instanceof Service) {
            $service = Service::find($service->getId());
        }

        return $service;
    }

    /**
     * Refresh the service & all of it's loaded relations.
     *
     * @return void
     */
    public function refresh()
    {
        $this->service->refresh();
    }

    /**
     * Resolve the providable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|string $provider
     * @return \Payavel\Orchestration\Contracts\Providable|null
     */
    public function resolveProvider($provider)
    {
        if (! $provider instanceof Provider) {
            $serviceProvider = config('orchestration.models.' . Provider::class, Provider::class);

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
     * @param \Payavel\Orchestration\Contracts\Merchantable|null $merchant
     * @return string|int
     */
    public function getDefaultProvider(Merchantable $merchant = null)
    {
        if (! $merchant instanceof Merchant || is_null($provider = $merchant->default_provider_id)) {
            $provider = $this->service->default_provider_id;
        }

        return $provider;
    }

    /**
     * Resolve the merchantable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Merchantable|string $merchant
     * @return \Payavel\Orchestration\Contracts\Merchantable|null
     */
    public function resolveMerchant($merchant)
    {
        if (! $merchant instanceof Merchant) {
            $serviceMerchant = config('orchestration.models.' . Merchant::class, Merchant::class);

            $merchant = $serviceMerchant::find($merchant);
        }

        if (is_null($merchant) || (! $merchant->exists)) {
            return null;
        }

        return $merchant;
    }

    /**
     * Get the default merchantable identifier.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|null $provider
     * @return string|int
     */
    public function getDefaultMerchant(Providable $provider = null)
    {
        return $this->service->default_merchant_id;
    }

    /**
     * Verify that the merchant is compatible with the provider.
     *
     * @param \Payavel\Orchestration\Contracts\Providable
     * @param \Payavel\Orchestration\Contracts\Merchantable
     * @return void
     * 
     * @throws Exception
     */
    protected function check($provider, $merchant)
    {
        if ($merchant->providers->contains($provider)) {
            return;
        }

        throw new Exception("The {$merchant->getName()} merchant is not supported by the {$provider->getName()} provider.");
    }

    /**
     * Resolve the gateway.
     *
     * @param \Payavel\Orchestration\Contracts\Providable $provider
     * @param \Payavel\Orchestration\Contracts\Merchantable $merchant
     * @return \Payavel\Orchestration\ServiceRequest
     * 
     * @throws Exception
     */
    public function resolveGateway($provider, $merchant)
    {
        $this->check($provider, $merchant);

        $gateway = $this->config($this->service->getId(), 'test_mode')
            ? $this->service->test_gateway
            : $provider->gateway;

        if (! class_exists($gateway)) {
            throw new Exception(
                is_null($gateway)
                    ? "You must set a gateway for the {$provider->getName()} {$this->service->getName()} provider."
                    : "The {$gateway}::class does not exist."
            );
        }

        return new $gateway($provider, $merchant);
    }

    /**
     * Generate the service skeleton based on the current driver.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable $service
     * @param array $config
     * @return void
     */
    public static function generateService(Serviceable $service, array $config)
    {
        static::putFile(
            config_path(Str::slug($service->getId()) . '.php'),
            static::makeFile(
                static::getStub('config-service-database'),
                [
                    'Title' => $service->getName(),
                    'Service' => Str::studly($service->getId()),
                    'service' => Str::lower($service->getName()),
                    'SERVICE' => Str::upper(Str::slug($service->getId(), '_')),
                    'provider' => $config['defaults']['provider'],
                    'merchant' => $config['defaults']['merchant'],
                ]
            )
        );

        // ToDo: Generate migrations here.
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
