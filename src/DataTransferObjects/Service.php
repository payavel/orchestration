<?php

namespace Payavel\Orchestration\DataTransferObjects;

use Payavel\Orchestration\Contracts\Serviceable;
use Payavel\Orchestration\Traits\SimulatesAttributes;

class Service implements Serviceable
{
    use SimulatesAttributes;

    public function __construct(array $data)
    {
        $this->attributes = $data;
    }

    /**
     * Get the service's id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->attributes['id'];
    }

    /**
     * Get the service's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->attributes['name'] ?? $this->attributes['id'];
    }
}
