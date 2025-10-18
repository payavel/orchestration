<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Facades\Config;

trait SetsConfigDriver
{
    /**
     * Set the driver.
     *
     * @return void
     */
    protected function setDriver(): void
    {
        Config::set('orchestration.defaults.driver', 'config');
    }
}
