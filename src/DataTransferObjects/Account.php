<?php

namespace Payavel\Orchestration\DataTransferObjects;

use Illuminate\Support\Collection;
use Payavel\Orchestration\Contracts\Accountable;
use Payavel\Orchestration\Fluent\FluentConfig;
use Payavel\Orchestration\Traits\SimulatesAttributes;
use Payavel\Orchestration\Support\ServiceConfig;

class Account implements Accountable
{
    use SimulatesAttributes;

    /**
     * The service config.
     *
     * @var \Payavel\Orchestration\Fluent\FluentConfig
     */
    public FluentConfig $config;

    public function __construct(FluentConfig $config, array $data)
    {
        $this->config = $config;

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
     * @return \Payavel\Orchestration\Fluent\FluentConfig
     */
    public function getServiceConfig()
    {
        return $this->config;
    }

    /**
     * Get the account's providers.
     *
     * @return mixed
     */
    public function getProviders()
    {
        if (! isset($this->providers)) {
            $this->attributes['providers'] = (new Collection(ServiceConfig::get($this->config, 'accounts.'.$this->attributes['id'].'.providers', [])))
                ->map(
                    fn ($provider, $key) => is_array($provider)
                        ? array_merge(
                            ['id' => $key],
                            ServiceConfig::get($this->config, 'providers.'.$key),
                            $provider
                        )
                        : array_merge(
                            ['id' => $provider],
                            ServiceConfig::get($this->config, 'providers.'.$provider)
                        )
                );
        }

        return $this->attributes['providers'];
    }
}
