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

    /**
     * Get the accountable service config.
     *
     * @return \Payavel\Orchestration\Fluent\FluentConfig
     */
    public function getServiceConfig();
}
