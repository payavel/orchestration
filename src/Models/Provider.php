<?php

namespace Payavel\Serviceable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Payavel\Serviceable\Contracts\Providable;
use Payavel\Serviceable\Database\Factories\ProviderFactory;
use Payavel\Serviceable\Traits\HasFactory;

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
        return $this->name;
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

        return config("payment.models.{$this->merchantModelClass}", $this->merchantModelClass);
    }


    /**
     * Guess the merchant model's class name by convention.
     *
     * @return string
     */
    private function guessMerchantModelClass()
    {
        $parentClass = get_class($this);

        do {
            $providerModelClass = $parentClass;

            $parentClass =  get_parent_class($providerModelClass);
        } while ($parentClass && $parentClass !== self::class);

        return Str::replace('Provider', 'Merchant', $parentClass);
    }
}
