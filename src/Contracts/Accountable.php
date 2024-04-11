<?php

namespace Payavel\Orchestration\Contracts;

interface Accountable extends Serviceable
{
    /**
     * Get the entity service.
     *
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function getService();
}
