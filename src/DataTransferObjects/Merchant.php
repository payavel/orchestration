<?php

namespace Payavel\Serviceable\DataTransferObjects;

use Illuminate\Support\Collection;
use Payavel\Serviceable\Contracts\Merchantable;
use Payavel\Serviceable\Traits\SimulateAttributes;

class Merchant implements Merchantable
{
    use SimulateAttributes;

    /**
     * Collection of providers this merchant is supported by.
     *
     * @var \Illuminate\Support\Collection
     */
    public $providers;

    public function __construct(array $data)
    {
        $this->attributes = $data;

        $this->providers = (new Collection($data['providers'] ?? []))->map(function ($provider, $key) {
            if (is_array($provider)) {
                return array_merge(
                    ['id' => $key],
                    config('serviceable.providers.' . $key),
                    $provider
                );
            }

            return array_merge(['id' => $provider], config('serviceable.providers.' . $provider));
        });
    }

    /**
     * Get the provider's id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->attributes['id'];
    }

    /**
     * Get the provider's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->attributes['name'];
    }
}
