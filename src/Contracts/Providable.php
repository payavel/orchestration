<?php

namespace Payavel\Orchestration\Contracts;

interface Providable
{
    /**
     * Gets the providable id.
     *
     * @return string|int
     */
    public function getId(): string|int;

    /**
     * Gets the providable name.
     *
     * @return string
     */
    public function getName(): string;
}
