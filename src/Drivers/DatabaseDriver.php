<?php

namespace Payavel\Orchestration\Drivers;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Models\Merchant;
use Payavel\Orchestration\Models\Provider;
use Payavel\Orchestration\ServiceDriver;
use Payavel\Orchestration\Traits\GeneratesFiles;
use Payavel\Orchestration\Support\ServiceConfig;

class DatabaseDriver extends ServiceDriver
{
    use GeneratesFiles;

    /**
     * Resolve the providable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|string $provider
     * @return \Payavel\Orchestration\Contracts\Providable|null
     */
    public function resolveProvider($provider)
    {
        if (! $provider instanceof Provider) {
            $serviceProvider = ServiceConfig::get($this->service, 'models.' . Provider::class, Provider::class);

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
            $provider = ServiceConfig::get($this->service, 'defaults.provider');
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
            $serviceMerchant = ServiceConfig::get($this->service, 'models.' . Merchant::class, Merchant::class);

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
        return ServiceConfig::get($this->service, 'defaults.merchant');
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

        $gateway = ServiceConfig::get($this->service, 'test_mode')
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
     * @param \Illuminate\Support\Collection $providers
     * @param \Illuminate\Support\Collection $merchants
     * @param array $defaults
     * @return void
     */
    public static function generateService(Serviceable $service, Collection $providers, Collection $merchants, array $defaults)
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
                    'provider' => $defaults['provider'],
                    'merchant' => $defaults['merchant'],
                ]
            )
        );

        $providers = $providers->reduce(
            fn ($array, $provider, $index) =>
                $array . static::makeFile(
                    static::getStub('migration-service-providers'),
                    [
                        'provider' => $provider['id'],
                        'gateway' => $provider['gateway'],
                    ]
                ) .
                ($index < count($providers) - 1 ? "\n" : ""),
            ""
        );

        $merchants = $merchants->reduce(
            fn ($array, $merchant, $index) =>
                $array . static::makeFile(
                    static::getStub('migration-service-merchants'),
                    [
                        'merchant' => $merchant['id'],
                        'providers' => implode(', ', array_map(fn ($provider) => "'$provider'", $merchant['providers'])),
                    ]
                ) .
                ($index < count($merchants) - 1 ? "\n" : ""),
            ""
        );

        static::putFile(
            database_path('migrations/' . Carbon::now()->format('Y_m_d_His') . '_add_providers_and_merchants_to_' . Str::slug($service->getId()) . '_service.php'),
            static::makeFile(
                static::getStub('migration-service'),
                [
                    'service' => $service->getId(),
                    'providers' => $providers,
                    'merchants' => $merchants,
                ]
            )
        );
    }
}
