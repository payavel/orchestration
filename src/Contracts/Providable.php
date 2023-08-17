<?php

namespace Payavel\Serviceable\Contracts;

interface Providable extends Serviceable
{
    /**
     * Get the entity service.
     *
     * @return \Payavel\Serviceable\Contracts\Serviceable
     */
    public function getService();
}
