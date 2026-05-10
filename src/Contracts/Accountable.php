<?php

namespace Payavel\Orchestration\Contracts;

interface Accountable
{
    /**
     * Gets the accountable id.
     *
     * @return string|int
     */
    public function getId(): string|int;

    /**
     * Gets the accountable name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Gets the accountable's provider configuration.
     */
    public function getConfig(Providable $provider): array;
}
