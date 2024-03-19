<?php

namespace Payavel\Orchestration\DataTransferObjects;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Merchantable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Traits\SimulatesAttributes;
use Payavel\Orchestration\Support\ServiceConfig;

class Merchant implements Merchantable
{
    use SimulatesAttributes;

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
            $this->attributes['providers'] = (new Collection(ServiceConfig::get($this->service, 'merchants.' . $this->attributes['id'] . '.providers', [])))
                ->map(
                    fn ($provider, $key) => is_array($provider)
                        ? array_merge(
                            ['id' => $key],
                            ServiceConfig::get($this->service, 'providers.' . $key),
                            $provider
                        )
                        : array_merge(
                            ['id' => $provider],
                            ServiceConfig::get($this->service, 'providers.' . $provider)
                        )
                );
        }

        return $this->attributes['providers'];
    }
}
