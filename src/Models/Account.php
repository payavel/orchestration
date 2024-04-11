<?php

namespace Payavel\Orchestration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Service;
use Payavel\Orchestration\Traits\HasFactory;
use Payavel\Orchestration\Support\ServiceConfig;

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
     * Get the account's id.
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the account's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name ?? $this->id;
    }

    /**
     * Get the entity's service.
     *
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function getService()
    {
        return Service::find($this->service_id);
    }

    /**
     * Get the providers that the account belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function providers()
    {
        return $this->belongsToMany($this->getProviderModelClass(), 'account_provider', 'account_id', 'provider_id')->withTimestamps();
    }

    /**
     * Get the provider model's class of this account's service.
     *
     * @return string
     */
    private function getProviderModelClass()
    {
        if(! isset($this->providerModelClass)) {
            $this->providerModelClass = $this->guessProviderModelClass();
        }

        return ServiceConfig::get($this->service_id, "models.{$this->providerModelClass}", $this->providerModelClass);
    }

    /**
     * Guess the provider model's class name by convention.
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
