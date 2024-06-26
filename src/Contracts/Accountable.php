<?php

namespace Payavel\Orchestration\Contracts;

interface Accountable
{
    /**
     * Get the accountable id.
     *
     * @return string|int
     */
    public function getId();

    /**
     * Get the accountable name.
     *
     * @return string
     */
    public function getName();
}
