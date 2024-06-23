<?php

namespace Payavel\Orchestration\Drivers;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Fluent\FluentConfig;
use Payavel\Orchestration\Models\Account;
use Payavel\Orchestration\Models\Provider;
use Payavel\Orchestration\ServiceDriver;
use Payavel\Orchestration\Traits\GeneratesFiles;

use function Laravel\Prompts\info;
use function Illuminate\Filesystem\join_paths;

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
            $serviceProvider = $this->serviceConfig->get('models.'.Provider::class, Provider::class);

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
     * @param \Payavel\Orchestration\Contracts\Accountable|null $account
     * @return string|int
     */
    public function getDefaultProvider(Accountable $account = null)
    {
        if (! $account instanceof Account || is_null($provider = $account->default_provider_id)) {
            $provider = $this->serviceConfig->get('defaults.provider');
        }

        return $provider;
    }

    /**
     * Resolve the accountable instance.
     *
     * @param \Payavel\Orchestration\Contracts\Accountable|string|int $account
     * @return \Payavel\Orchestration\Contracts\Accountable|null
     */
    public function resolveAccount($account)
    {
        if (! $account instanceof Account) {
            $accountModel = $this->serviceConfig->get('models.'.Account::class, Account::class);

            $account = $accountModel::find($account);
        }

        if (is_null($account) || (! $account->exists)) {
            return null;
        }

        return $account;
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
        if ($account->providers->contains($provider)) {
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
            ? $this->serviceConfig->test_gateway
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
     * @param \Payavel\Orchestration\Fluent\FluentConfig $serviceConfig
     * @param \Illuminate\Support\Collection $providers
     * @param \Illuminate\Support\Collection $accounts
     * @param array $defaults
     * @return void
     */
    public static function generateService(FluentConfig $serviceConfig, Collection $providers, Collection $accounts, array $defaults)
    {
        Artisan::call('vendor:publish', ['--tag' => 'payavel-orchestration-migrations']);

        static::putFile(
            config_path($configPath = Str::slug($serviceConfig->id).'.php'),
            static::makeFile(
                static::getStub('config-service-database', $serviceConfig->id),
                [
                    'Title' => $serviceConfig->name,
                    'Service' => Str::studly($serviceConfig->id),
                    'service' => Str::lower($serviceConfig->name),
                    'SERVICE' => Str::upper(Str::slug($serviceConfig->id, '_')),
                    'driver' => $defaults['driver'],
                    'provider' => $defaults['provider'],
                    'account' => $defaults['account'],
                ]
            )
        );

        info('Config ['.join_paths('config', $configPath).'] created successfully.');

        Config::set(Str::slug($serviceConfig->id), require(config_path($configPath)));

        $providers = $providers->reduce(
            fn ($array, $provider, $index) =>
                $array.static::makeFile(
                    static::getStub('migration-service-providers', $serviceConfig->id),
                    [
                        'id' => $provider['id'],
                        'name' => $provider['name'],
                        'gateway' => $provider['gateway'],
                    ]
                ) .
                ($index < count($providers) - 1 ? "\n" : ""),
            ""
        );

        $accounts = $accounts->reduce(
            fn ($array, $account, $index) =>
                $array.static::makeFile(
                    static::getStub('migration-service-accounts', $serviceConfig->id),
                    [
                        'id' => $account['id'],
                        'name' => $account['name'],
                        'providers' => implode(', ', array_map(fn ($provider) => "'$provider'", $account['providers'])),
                    ]
                ) .
                ($index < count($accounts) - 1 ? "\n" : ""),
            ""
        );

        static::putFile(
            database_path($migrationPath = join_paths('migrations', Carbon::now()->format('Y_m_d_His').'_add_providers_and_accounts_to_'.Str::slug($serviceConfig->id, '_')).'_service.php'),
            static::makeFile(
                static::getStub('migration-service', $serviceConfig->id),
                [
                    'service' => $serviceConfig->id,
                    'providers' => $providers,
                    'accounts' => $accounts,
                ]
            )
        );

        info("Migration [database/{$migrationPath}] created successfully.");
    }
}
