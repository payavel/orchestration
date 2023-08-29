<?php

namespace Payavel\Serviceable\DataTransferObjects;

use Illuminate\Support\Str;
use Payavel\Serviceable\Contracts\Serviceable;
use Payavel\Serviceable\Traits\SimulatesAttributes;

class Service implements Serviceable
{
    use SimulatesAttributes;

    public function __construct(array $data)
    {
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
}
