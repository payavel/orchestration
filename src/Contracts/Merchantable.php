<?php

namespace Payavel\Orchestration\Contracts;

interface Merchantable extends Serviceable
{
    /**
     * Get the entity service.
     *
     * @return \Payavel\Orchestration\Contracts\Serviceable
     */
    public function getService();
}
