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

    public function __construct(Serviceable $service, array $data)
    {
        $this->service = $service;

        $this->attributes = $data;
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

    /**
     * Get the entity service.
     *
     * @return \Payavel\Serviceable\Contracts\Serviceable
     */
    public function getService()
    {
        return $this->service;
    }

    public function getProviders()
    {
        if (! isset($this->providers)) {
            $this->attributes['providers'] = (new Collection($this->config($this->service->getId(), 'merchants.' . $this->attributes['id'] . '.providers', [])))
                ->map(function ($provider, $key) {
                    if (is_array($provider)) {
                        return array_merge(
                            ['id' => $key],
                            $this->config($this->service->getId(), 'providers.' . $key),
                            $provider
                        );
                    }

                    return array_merge(['id' => $provider], $this->config($this->service->getId(), 'providers.' . $provider));
                });
        }

        return $this->attributes['providers'];
    }
}
