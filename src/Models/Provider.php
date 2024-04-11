<?php

namespace Payavel\Orchestration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Service;
use Payavel\Orchestration\Traits\HasFactory;
use Payavel\Orchestration\Support\ServiceConfig;

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
     * Get the provider's id.
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the provider's name.
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
     * Get the account's the provider supports.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function accounts()
    {
        return $this->belongsToMany($this->getAccountModelClass(), 'account_provider', 'provider_id', 'account_id')->withTimestamps();
    }

    /**
     * Get the account model's class of this provider's service.
     *
     * @return string
     */
    private function getAccountModelClass()
    {
        if(! isset($this->accountModelClass)) {
            $this->accountModelClass = $this->guessAccountModelClass();
        }

        return ServiceConfig::get($this->service_id, "models.{$this->accountModelClass}", $this->accountModelClass);
    }


    /**
     * Guess the account model's class name by convention.
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
