<?php

namespace Payavel\Orchestration\DataTransferObjects;

use Illuminate\Support\Str;
use Payavel\Orchestration\Contracts\Providable;
use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Traits\SimulatesAttributes;

class Provider implements Providable
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
}
