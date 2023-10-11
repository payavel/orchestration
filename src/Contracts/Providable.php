<?php

namespace Payavel\Orchestration\Contracts;

interface Providable extends Serviceable
{
    /**
     * Get the entity service.
     *
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function getService();
}
