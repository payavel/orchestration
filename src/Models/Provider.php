<?php

namespace Payavel\Orchestration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\ServiceConfig;
use Payavel\Orchestration\Traits\HasFactory;

class Provider extends Model implements Providable
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'config_format' => 'array',
    ];

    /**
     * The service config.
     *
     * @var \Payavel\Orchestration\ServiceConfig
     */
    protected ServiceConfig $serviceConfig;

    /**
     * Gets the providable id.
     *
     * @return string|int
     */
    public function getId(): string|int
    {
        return $this->id;
    }

    /**
     * Gets the providable name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? $this->id;
    }

    /**
     * Gets the provider's related accounts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany($this->getAccountModelClass(), 'account_provider', 'provider_id', 'account_id')->using($this->getAccountProviderPivotClass())->withTimestamps();
    }

    /**
     * Gets the account model class relative to the provider.
     *
     * @return string
     */
    protected function getAccountModelClass(): string
    {
        if (!isset($this->accountModelClass)) {
            $this->accountModelClass = $this->guessAccountModelClass();
        }

        if (!isset($this->serviceConfig)) {
            $this->serviceConfig = ServiceConfig::find($this->service_id);
        }

        return $this->serviceConfig->get("models.{$this->accountModelClass}", $this->accountModelClass);
    }


    /**
     * Guesses the account model class name by convention.
     *
     * @return string
     */
    protected function guessAccountModelClass(): string
    {
        $parentClass = get_class($this);

        if ($parentClass === self::class) {
            return Account::class;
        }

        do {
            $providerModelClass = $parentClass;

            $parentClass =  get_parent_class($providerModelClass);
        } while ($parentClass && $parentClass !== self::class);

        return Str::replace('Provider', 'Account', $parentClass);
    }

    /**
     * Gets the account provider pivot class.
     *
     * @return string
     */
    protected function getAccountProviderPivotClass(): string
    {
        if (!isset($this->accountProviderPivotClass)) {
            $this->accountProviderPivotClass = $this->guessAccountProviderPivotClass();
        }

        if (!isset($this->serviceConfig)) {
            $this->serviceConfig = ServiceConfig::find($this->service_id);
        }

        return $this->serviceConfig->get("models.{$this->accountProviderPivotClass}", $this->accountProviderPivotClass);
    }

    /**
     * Guesses the account provider pivot class name by convention.
     *
     * @return string
     */
    protected function guessAccountProviderPivotClass(): string
    {
        $parentClass = get_class($this);

        if ($parentClass === self::class) {
            return AccountProvider::class;
        }

        do {
            $providerModelClass = $parentClass;

            $parentClass =  get_parent_class($providerModelClass);
        } while ($parentClass && $parentClass !== self::class);

        return Str::replace('Provider', 'AccountProvider', $providerModelClass);
    }
}
