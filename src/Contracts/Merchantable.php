<?php

namespace Payavel\Serviceable\Contracts;

interface Merchantable extends Serviceable
{
    /**
     * Get the entity service.
     *
     * @return \Payavel\Serviceable\Contracts\Serviceable
     */
    public function getService();
}
