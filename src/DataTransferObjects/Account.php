<?php

namespace Payavel\Orchestration\DataTransferObjects;

use Illuminate\Support\Collection;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Fluent\ServiceConfig;
use Payavel\Orchestration\Traits\SimulatesAttributes;

class Account implements Accountable
{
    use SimulatesAttributes;

    /**
     * The service config.
     *
     * @var \Payavel\Orchestration\Fluent\ServiceConfig
     */
    public ServiceConfig $serviceConfig;

    public function __construct(ServiceConfig $serviceConfig, array $data)
    {
        $this->serviceConfig = $serviceConfig;

        $this->attributes = $data;
    }

    /**
     * Get the accountable id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->attributes['id'];
    }

    /**
     * Get the accountable name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->attributes['name'] ?? $this->attributes['id'];
    }

    /**
     * Get the accountable service config.
     *
     * @return \Payavel\Orchestration\Fluent\ServiceConfig
     */
    public function getServiceConfig()
    {
        return $this->serviceConfig;
    }

    /**
     * Get the account's providers.
     *
     * @return mixed
     */
    public function getProviders()
    {
        if (! isset($this->providers)) {
            $this->attributes['providers'] = (new Collection($this->serviceConfig->get('accounts.'.$this->attributes['id'].'.providers', [])))
                ->map(
                    fn ($provider, $key) => is_array($provider)
                        ? array_merge(
                            ['id' => $key],
                            $this->serviceConfig->get('providers.'.$key),
                            $provider
                        )
                        : array_merge(
                            ['id' => $provider],
                            $this->serviceConfig->get('providers.'.$provider)
                        )
                );
        }

        return $this->attributes['providers'];
    }
}
