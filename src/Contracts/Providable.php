<?php

namespace Payavel\Serviceable\Contracts;

interface Providable extends Serviceable
{
    /**
     * Get the entity service.
     *
     * @return string|int
     */
    public function getService();
}
