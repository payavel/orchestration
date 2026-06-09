<?php

namespace Payavel\Orchestration\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\ServiceConfig;
use Payavel\Orchestration\Traits\HasFactory;

class Account extends Model implements Accountable
{
    use HasFactory;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * The service config.
     *
     * @var \Payavel\Orchestration\ServiceConfig
     */
    protected ServiceConfig $serviceConfig;

    /**
     * Gets the accountable id.
     *
     * @return string|int
     */
    public function getId(): string|int
    {
        return $this->id;
    }

    /**
     * Gets the accountable name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? $this->id;
    }

    /**
     * Gets the accountable's provider configuration.
     *
     * @param \Payavel\Orchestration\Contracts\Providable $provider
     * @return array
     */
    public function getConfig(Providable $provider): array
    {
        $config = $this->providers()->where('provider_id', $provider->getId())->first()?->pivot->config ?? [];

        return array_map(function ($value) {
            try {
                return Crypt::decrypt($value);
            } catch (DecryptException $e) {
                return $value;
            }
        }, $config);
    }

    public function setConfig(Providable $provider, array $config, array $valuesToEncrypt = []): void
    {
        foreach ($valuesToEncrypt as $valueToEncrypt) {
            if (!isset($config[$valueToEncrypt])) {
                continue;
            }

            try {
                Crypt::decrypt($config[$valueToEncrypt]);
            } catch (DecryptException $e) {
                $config[$valueToEncrypt] = Crypt::encrypt($config[$valueToEncrypt]);
            }
        }

        $this->providers()->syncWithoutDetaching([
            $provider->getId() => [
                'config' => $config,
            ],
        ]);
    }

    /**
     * Gets the account's related providers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function providers(): BelongsToMany
    {
        return $this->belongsToMany($this->getProviderModelClass(), 'account_provider', 'account_id', 'provider_id')->withPivot('config')->withTimestamps();
    }

    /**
     * Gets the provider model class relative to the account.
     *
     * @return string
     */
    protected function getProviderModelClass(): string
    {
        if (!isset($this->providerModelClass)) {
            $this->providerModelClass = $this->guessProviderModelClass();
        }

        if (!isset($this->serviceConfig)) {
            $this->serviceConfig = ServiceConfig::find($this->service_id);
        }

        return $this->serviceConfig->get("models.{$this->providerModelClass}", $this->providerModelClass);
    }

    /**
     * Guesses the provider model class name by convention.
     *
     * @return string
     */
    protected function guessProviderModelClass(): string
    {
        $parentClass = get_class($this);

        if ($parentClass === self::class) {
            return Provider::class;
        }

        do {
            $accountModelClass = $parentClass;

            $parentClass =  get_parent_class($accountModelClass);
        } while ($parentClass && $parentClass !== self::class);

        return Str::replace('Account', 'Provider', $accountModelClass);
    }
}
