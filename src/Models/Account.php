<?php

namespace Payavel\Orchestration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Fluent\FluentConfig;
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
     * @var string[]|bool
     */
    protected $guarded = [];

    /**
     * Get the accountable id.
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the accountable name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name ?? $this->id;
    }

    /**
     * Get the accountable service config.
     *
     * @return \Payavel\Orchestration\Fluent\FluentConfig
     */
    public function getServiceConfig()
    {
        return FluentConfig::find($this->service_id);
    }

    /**
     * Get the account's related providers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function providers()
    {
        return $this->belongsToMany($this->getProviderModelClass(), 'account_provider', 'account_id', 'provider_id')->withTimestamps();
    }

    /**
     * Get the provider model class relative to the account.
     *
     * @return string
     */
    private function getProviderModelClass()
    {
        if(! isset($this->providerModelClass)) {
            $this->providerModelClass = $this->guessProviderModelClass();
        }

        return $this->getServiceConfig()->get("models.{$this->providerModelClass}", $this->providerModelClass);
    }

    /**
     * Guess the provider model class name by convention.
     *
     * @return string
     */
    private function guessProviderModelClass()
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
