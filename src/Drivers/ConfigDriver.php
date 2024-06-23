<?php

namespace Payavel\Orchestration\Drivers;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\DataTransferObjects\Account;
use Payavel\Orchestration\DataTransferObjects\Provider;
use Payavel\Orchestration\Fluent\ServiceConfig;
use Payavel\Orchestration\ServiceDriver;
use Payavel\Orchestration\Traits\GeneratesFiles;

use function Laravel\Prompts\info;
use function Illuminate\Filesystem\join_paths;

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
    public function __construct(ServiceConfig $serviceConfig)
    {
        parent::__construct($serviceConfig);

        $this->providers = collect($this->serviceConfig->get('providers'));
        $this->accounts = collect($this->serviceConfig->get('accounts'));
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
            $this->serviceConfig,
            array_merge(['id' => $provider], $attributes)
        );
    }

    /**
     * Get the default providable identifier.
     *
     * @param \Payavel\Orchestration\Contracts\Accountable|null $account
     * @return string|int
     */
    public function getDefaultProvider(Accountable $account = null)
    {
        if (
            ! $account instanceof Account ||
            is_null($provider = $account->providers->first())
        ) {
            return $this->serviceConfig->get('defaults.provider');
        }

        return $provider['id'];
    }

    /**
     * Resolve the accountable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Accountable|string|int $account
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
            $this->serviceConfig,
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
        return $this->serviceConfig->get('defaults.account');
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
    protected function check(Providable $provider, Accountable $account)
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
    public function resolveGateway(Providable $provider, Accountable $account)
    {
        $this->check($provider, $account);

        $gateway = $this->serviceConfig->get('test_mode')
            ? $this->serviceConfig->get('test_gateway')
            : $provider->gateway;

        if (! class_exists($gateway)) {
            throw new Exception(
                is_null($gateway)
                    ? "You must set a gateway for the {$provider->getName()} {$this->serviceConfig->name} provider."
                    : "The {$gateway}::class does not exist."
            );
        }

        return new $gateway($provider, $account);
    }

    /**
     * Generate the service skeleton based on the current driver.
     *
     * @param \Payavel\Orchestration\Fluent\ServiceConfig $serviceConfig
     * @param \Illuminate\Support\Collection $providers
     * @param \Illuminate\Support\Collection $accounts
     * @param array $defaults
     * @return void
     */
    public static function generateService(ServiceConfig $serviceConfig, Collection $providers, Collection $accounts, array $defaults)
    {
        $data = [];

        $data['providers'] = $providers->reduce(
            fn ($stub, $provider) =>
                $stub .
                static::makeFile(
                    static::getStub('config-service-provider', $serviceConfig->id),
                    [
                        'id' => $provider['id'],
                        'name' => $provider['name'],
                        'gateway' => $provider['gateway'],
                    ]
                ),
            ""
        );

        $data['accounts'] = $accounts->reduce(
            fn ($stub, $account) =>
                $stub.static::makeFile(
                    static::getStub('config-service-account', $serviceConfig->id),
                    [
                        'id' => $account['id'],
                        'name' => $account['name'],
                        'providers' => Collection::make($account['providers'])->reduce(
                            fn ($stub, $provider, $index) =>
                                $stub .
                                static::makeFile(
                                    static::getStub('config-service-account-providers', $serviceConfig->id),
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
            config_path($configPath = Str::slug($serviceConfig->id).'.php'),
            static::makeFile(
                static::getStub('config-service', $serviceConfig->id),
                [
                    'Title' => $serviceConfig->id,
                    'Service' => Str::studly($serviceConfig->id),
                    'service' => Str::lower($serviceConfig->id),
                    'SERVICE' => Str::upper(Str::slug($serviceConfig->id, '_')),
                    'driver' => $defaults['driver'],
                    'provider' => $defaults['provider'],
                    'providers' => $data['providers'],
                    'account' => $defaults['account'],
                    'accounts' => $data['accounts'],
                ]
            )
        );

        info('Config ['.join_paths('config', $configPath).'] created successfully.');

        Config::set(Str::slug($serviceConfig->id), require(config_path($configPath)));
    }
}
