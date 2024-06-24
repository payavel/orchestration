<?php

namespace Payavel\Orchestration\DataTransferObjects;

use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Fluent\ServiceConfig;
use Payavel\Orchestration\Traits\SimulatesAttributes;

class Provider implements Providable
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
     * @return \Payavel\Orchestration\Fluent\ServiceConfig
     */
    public function getServiceConfig()
    {
        return $this->serviceConfig;
    }
}
