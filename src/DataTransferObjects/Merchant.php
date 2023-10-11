<?php

namespace Payavel\Orchestration\DataTransferObjects;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Traits\ServesConfig;
use Payavel\Orchestration\Traits\SimulatesAttributes;

class Merchant implements Merchantable
{
    use ServesConfig,
        SimulatesAttributes;

    /**
     * The compatible service.
     *
     * @var \Payavel\Orchestration\Contracts\Serviceable
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
        return Str::headline($this->attributes['id']);
    }

    /**
     * Get the entity service.
     *
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function getService()
    {
        return $this->service;
    }

    public function getProviders()
    {
        if (! isset($this->providers)) {
            $this->attributes['providers'] = (new Collection($this->config($this->service->getId(), 'merchants.' . $this->attributes['id'] . '.providers', [])))
                ->map(fn ($provider, $key) =>
                    is_array($provider)
                        ? array_merge(
                            ['id' => $key],
                            $this->config($this->service->getId(), 'providers.' . $key),
                            $provider
                        )
                        : array_merge(
                            ['id' => $provider],
                            $this->config($this->service->getId(), 'providers.' . $provider)
                        )
                );
        }

        return $this->attributes['providers'];
    }
}
