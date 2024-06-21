<?php

namespace Payavel\Orchestration\DataTransferObjects;

use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Fluent\FluentConfig;
use Payavel\Orchestration\Traits\SimulatesAttributes;

class Provider implements Providable
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
     * Get the providable id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->attributes['id'];
    }

    /**
     * Get the providable name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->attributes['name'] ?? $this->attributes['id'];
    }

    /**
     * Get the providable service config.
     *
     * @return \Payavel\Orchestration\Fluent\FluentConfig
     */
    public function getServiceConfig()
    {
        return $this->config;
    }
}
