<?php

namespace Payavel\Serviceable\Contracts;

interface Merchantable extends Serviceable
{
    /**
     * Get the entity service.
     *
     * @return string|int
     */
    public function getService();
}
