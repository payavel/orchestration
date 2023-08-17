<?php

namespace Payavel\Serviceable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Payavel\Serviceable\Contracts\Merchantable;
use Payavel\Serviceable\Traits\HasFactory;
use Payavel\Serviceable\Traits\ServiceableConfig;

class Merchant extends Model implements Merchantable
{
    use HasFactory,
        ServiceableConfig;

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
     * Get the merchant's id.
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the merchant's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the entity service.
     *
     * @return \Payavel\Serviceable\Contracts\Serviceable
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Get the service this merchant belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo($this->config($this->service_id, 'models.' . Service::class, Service::class));
    }

    /**
     * Get the providers that the merchant belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function providers()
    {
        return $this->belongsToMany($this->getProviderModelClass(), 'merchant_provider', 'merchant_id', 'provider_id')->withPivot(['default'])->withTimestamps();
    }

    /**
     * Get the provider model's class of this merchant's service.
     *
     * @return string
     */
    private function getProviderModelClass()
    {
        if(! isset($this->providerModelClass)) {
            $this->providerModelClass = $this->guessProviderModelClass();
        }

        return $this->config($this->service_id, "models.{$this->providerModelClass}", $this->providerModelClass);
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
            $merchantModelClass = $parentClass;

            $parentClass =  get_parent_class($merchantModelClass);
        } while ($parentClass && $parentClass !== self::class);

        return Str::replace('Merchant', 'Provider', $merchantModelClass); 
    }
}
