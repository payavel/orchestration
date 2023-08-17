<?php

namespace Payavel\Serviceable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Payavel\Serviceable\Contracts\Providable;
use Payavel\Serviceable\Database\Factories\ProviderFactory;
use Payavel\Serviceable\Traits\HasFactory;
use Payavel\Serviceable\Traits\ServiceableConfig;

class Provider extends Model implements Providable
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
     * Get the service this provider belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo($this->config('models.' . Service::class, Service::class));
    }

    /**
     * Get the merchant's the provider supports.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function merchants()
    {
        return $this->belongsToMany($this->getMerchantModelClass(), 'merchant_provider', 'provider_id', 'merchant_id')->withPivot(['default'])->withTimestamps();
    }

    /**
     * Get the merchant model's class of this provider's service.
     *
     * @return string
     */
    private function getMerchantModelClass()
    {
        if(! isset($this->merchantModelClass)) {
            $this->merchantModelClass = $this->guessMerchantModelClass();
        }

        return $this->config("models.{$this->merchantModelClass}", $this->merchantModelClass);
    }


    /**
     * Guess the merchant model's class name by convention.
     *
     * @return string
     */
    private function guessMerchantModelClass()
    {
        $parentClass = get_class($this);

        if ($parentClass === self::class) {
            return Merchant::class;
        }

        do {
            $providerModelClass = $parentClass;

            $parentClass =  get_parent_class($providerModelClass);
        } while ($parentClass && $parentClass !== self::class);

        return Str::replace('Provider', 'Merchant', $parentClass);
    }
}
