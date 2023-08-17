<?php

namespace Payavel\Serviceable\DataTransferObjects;

use Payavel\Serviceable\Contracts\Providable;
use Payavel\Serviceable\Contracts\Serviceable;
use Payavel\Serviceable\Traits\ServiceableConfig;
use Payavel\Serviceable\Traits\SimulateAttributes;

class Provider implements Providable
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
}
