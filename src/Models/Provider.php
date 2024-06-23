<?php

namespace Payavel\Orchestration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Fluent\FluentConfig;
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
     * Get the providable service config.
     *
     * @return \Payavel\Orchestration\Fluent\FluentConfig
     */
    public function getServiceConfig()
    {
        return FluentConfig::find($this->service_id);
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
    private function getAccountModelClass()
    {
        if(! isset($this->accountModelClass)) {
            $this->accountModelClass = $this->guessAccountModelClass();
        }

        return $this->getServiceConfig()->get("models.{$this->accountModelClass}", $this->accountModelClass);
    }


    /**
     * Guess the account model class name by convention.
     *
     * @return string
     */
    private function guessAccountModelClass()
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
