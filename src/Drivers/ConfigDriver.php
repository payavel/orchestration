<?php

namespace Payavel\Orchestration\Drivers;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\DataTransferObjects\Account;
use Payavel\Orchestration\DataTransferObjects\Provider;
use Payavel\Orchestration\ServiceDriver;
use Payavel\Orchestration\Traits\GeneratesFiles;
use Payavel\Orchestration\Support\ServiceConfig;

use function Laravel\Prompts\info;

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
     * Collection of the service's accounts.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $accounts;


    /**
     * Collect the service's providers & accounts.
     */
    public function __construct(Serviceable $service)
    {
        parent::__construct($service);

        $this->providers = collect(ServiceConfig::get($this->service, 'providers'));
        $this->accounts = collect(ServiceConfig::get($this->service, 'accounts'));
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
     * @param \Payavel\Orchestration\Contracts\Accountable|null $account
     * @return string|int|\Payavel\Orchestration\Contracts\Providable
     */
    public function getDefaultProvider(Accountable $account = null)
    {
        if (
            ! $account instanceof Account ||
            is_null($provider = $account->providers->first())
        ) {
            return ServiceConfig::get($this->service, 'defaults.provider');
        }

        return $provider['id'];
    }

    /**
     * Resolve the accountable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Accountable|string $account
     * @return \Payavel\Orchestration\Contracts\Accountable|null
     */
    public function resolveAccount($account)
    {
        if ($account instanceof Account) {
            return $account;
        }

        if (is_null($attributes = $this->accounts->get($account))) {
            return null;
        }

        return new Account(
            $this->service,
            array_merge(['id' => $account], $attributes)
        );
    }

    /**
     * Get the default accountable identifier.
     *
     * @param \Payavel\Orchestration\Contracts\Providable|null $provider
     * @return string|int
     */
    public function getDefaultAccount(Providable $provider = null)
    {
        return ServiceConfig::get($this->service, 'defaults.account');
    }

    /**
     * Verify that the account is compatible with the provider.
     *
     * @param \Payavel\Orchestration\Contracts\Providable
     * @param \Payavel\Orchestration\Contracts\Accountable
     * @return void
     *
     * @throws Exception
     */
    protected function check($provider, $account)
    {
        if ($account->providers->contains('id', $provider->id)) {
            return;
        }

        throw new Exception("The {$account->getName()} account is not supported by the {$provider->getName()} provider.");
    }

    /**
     * Resolve the gateway.
     *
     * @param \Payavel\Orchestration\Contracts\Providable $provider
     * @param \Payavel\Orchestration\Contracts\Accountable $account
     * @return \Payavel\Orchestration\ServiceRequest
     *
     * @throws Exception
     */
    public function resolveGateway($provider, $account)
    {
        $this->check($provider, $account);

        $gateway = ServiceConfig::get($this->service, 'test_mode')
            ? ServiceConfig::get($this->service, 'testing.gateway')
            : $provider->gateway;

        if (! class_exists($gateway)) {
            throw new Exception(
                is_null($gateway)
                    ? "You must set a gateway for the {$provider->getName()} {$this->service->getName()} provider."
                    : "The {$gateway}::class does not exist."
            );
        }

        return new $gateway($provider, $account);
    }

    /**
     * Generate the service skeleton based on the current driver.
     *
     * @param \Payavel\Orchestration\Contracts\Serviceable $service
     * @param \Illuminate\Support\Collection $providers
     * @param \Illuminate\Support\Collection $accounts
     * @param array $defaults
     * @return void
     */
    public static function generateService(Serviceable $service, Collection $providers, Collection $accounts, array $defaults)
    {
        $config = [];

        $config['providers'] = $providers->reduce(
            fn ($config, $provider) =>
                $config .
                static::makeFile(
                    static::getStub('config-service-provider', $service->getId()),
                    [
                        'id' => $provider['id'],
                        'name' => $provider['name'],
                        'gateway' => $provider['gateway'],
                    ]
                ),
            ""
        );

        $config['accounts'] = $accounts->reduce(
            fn ($config, $account) =>
                $config . static::makeFile(
                    static::getStub('config-service-account', $service->getId()),
                    [
                        'id' => $account['id'],
                        'name' => $account['name'],
                        'providers' => Collection::make($account['providers'])->reduce(
                            fn ($config, $provider, $index) =>
                                $config .
                                static::makeFile(
                                    static::getStub('config-service-account-providers', $service->getId()),
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
            config_path($configPath = Str::slug($service->getId()) . '.php'),
            static::makeFile(
                static::getStub('config-service', $service->getId()),
                [
                    'Title' => $service->getName(),
                    'Service' => Str::studly($service->getId()),
                    'service' => Str::lower($service->getName()),
                    'SERVICE' => Str::upper(Str::slug($service->getId(), '_')),
                    'driver' => $defaults['driver'],
                    'provider' => $defaults['provider'],
                    'providers' => $config['providers'],
                    'account' => $defaults['account'],
                    'accounts' => $config['accounts'],
                ]
            )
        );

        info('Config [.'join_paths('config', $configPath).'] created successfully.');

        Config::set(Str::slug($service->getId()), require(config_path($configPath)));
    }
}
