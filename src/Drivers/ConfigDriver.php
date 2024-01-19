<?php

namespace Payavel\Orchestration\Drivers;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\DataTransferObjects\Merchant;
use Payavel\Orchestration\DataTransferObjects\Provider;
use Payavel\Orchestration\DataTransferObjects\Service;
use Payavel\Orchestration\ServiceDriver;
use Payavel\Orchestration\Traits\GeneratesFiles;

class ConfigDriver extends ServiceDriver
{
    use GeneratesFiles;

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
     * @return void
     * 
     * @throws Exception
     */
    protected function check($provider, $merchant)
    {
        if ($merchant->providers->contains('id', $provider->id)) {
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
            ? $this->config($this->service->getId(), 'testing.gateway')
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
        $config = [];

        $config['providers'] = $providers->reduce(
            fn ($config, $provider) =>
                $config .
                static::makeFile(
                    static::getStub('config-service-provider'),
                    [
                        'id' => $provider['id'],
                        'gateway' => $provider['gateway'],
                    ]
                ),
            ""
        );

        $config['merchants'] = $merchants->reduce(
            fn ($config, $merchant) =>
                $config . static::makeFile(
                    static::getStub('config-service-merchant'),
                    [
                        'id' => $merchant['id'],
                        'providers' => Collection::make($merchant['providers'])->reduce(
                            fn ($config, $provider, $index) =>
                                $config .
                                static::makeFile(
                                    static::getStub('config-service-merchant-providers'),
                                    ['id' => $provider]
                                ) .
                                ($index < count($providers) - 1 ? "\n" : ""),
                                ""
                        )
                    ]
                ),
                ""
        );

        static::putFile(
            config_path(Str::slug($service->getId()) . '.php'),
            static::makeFile(
                static::getStub('config-service'),
                [
                    'Title' => $service->getName(),
                    'Service' => Str::studly($service->getId()),
                    'service' => Str::lower($service->getName()),
                    'SERVICE' => Str::upper(Str::slug($service->getId(), '_')),
                    'provider' => $defaults['provider'],
                    'providers' => $config['providers'],
                    'merchant' => $defaults['merchant'],
                    'merchants' => $config['merchants'],
                ]
            )
        );
    }

    /**
     * Get a collection of existing serviceables.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function services()
    {
        return collect(Config::get('orchestration.services', []))->map(
            fn ($value, $key) => new Service(array_merge(['id' => $key], is_array($value) ? $value : Config::get($value)))
        )->values();
    }
}
