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

    /**
     * Get the providable service config.
     *
     * @return \Payavel\Orchestration\Fluent\ServiceConfig
     */
    public function getServiceConfig();
}
