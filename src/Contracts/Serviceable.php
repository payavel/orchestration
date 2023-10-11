<?php

namespace Payavel\Orchestration\Contracts;

interface Serviceable
{
    /**
     * Get the entity id.
     *
     * @return string|int
     */
    public function getId();

    /**
     * Get the entity name.
     *
     * @return string
     */
    public function getName();
}
