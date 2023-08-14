<?php

namespace Payavel\Serviceable\DataTransferObjects;

use Illuminate\Support\Collection;
use Payavel\Serviceable\Contracts\Merchantable;
use Payavel\Serviceable\Contracts\Serviceable;
use Payavel\Serviceable\Traits\ServiceableConfig;
use Payavel\Serviceable\Traits\SimulateAttributes;

class Merchant implements Merchantable
{
    use ServiceableConfig,
        SimulateAttributes;

    /**
     * The compatible service.
     *
     * @var \Payavel\Serviceable\Contracts\Serviceable
     */
    public Serviceable $service;

    /**
     * Collection of providers this merchant is supported by.
     *
     * @var \Payavel\Serviceable\Contracts\Serviceable $service
     * @var \Illuminate\Support\Collection
     */
    public $providers;

    public function __construct(Serviceable $service, array $data)
    {
        $this->service = $service;

        $this->attributes = $data;

        $this->providers = (new Collection($data['providers'] ?? []))->map(function ($provider, $key) {
            if (is_array($provider)) {
                return array_merge(
                    ['id' => $key],
                    $this->config('providers.' . $key),
                    $provider
                );
            }

            return array_merge(['id' => $provider], $this->config('providers.' . $provider));
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
