<?php

namespace Payavel\Orchestration\Contracts;

interface Providable
{
    /**
     * Get the providable id.
     *
     * @return string|int
     */
    public function getId();

    /**
     * Get the providable name.
     *
     * @return string
     */
    public function getName();
}
