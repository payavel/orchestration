<?php

namespace Payavel\Orchestration\DataTransferObjects;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
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
     * Create a new static instance from a serviceable.
     *
     * @param Serviceable $service
     * @return static
     */
    public static function fromServiceable(Serviceable $service)
    {
        return new static(array_merge(
            ['id' => $service->getId()],
            Config::get('orchestration.services.' . $service->getId(), [])
        ));
    }
}
