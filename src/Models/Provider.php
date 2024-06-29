<?php

namespace Payavel\Orchestration\Models;

use Illuminate\Database\Eloquent\Model;
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
     * @var string[]|bool
     */
    protected $guarded = [];

    /**
     * The service config.
     *
     * @var \Payavel\Orchestration\ServiceConfig
     */
    protected ServiceConfig $serviceConfig;

    /**
     * Get the providable id.
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the providable name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name ?? $this->id;
    }

    /**
     * Get the provider's related accounts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function accounts()
    {
        return $this->belongsToMany($this->getAccountModelClass(), 'account_provider', 'provider_id', 'account_id')->withTimestamps();
    }

    /**
     * Get the account model class relative to the provider.
     *
     * @return string
     */
    protected function getAccountModelClass()
    {
        if(!isset($this->accountModelClass)) {
            $this->accountModelClass = $this->guessAccountModelClass();
        }

        if (!isset($this->serviceConfig)) {
            $this->serviceConfig = ServiceConfig::find($this->service_id);
        }

        return $this->serviceConfig->get("models.{$this->accountModelClass}", $this->accountModelClass);
    }


    /**
     * Guess the account model class name by convention.
     *
     * @return string
     */
    protected function guessAccountModelClass()
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
}
