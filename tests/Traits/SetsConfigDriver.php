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
    protected function setDriver()
    {
        Config::set('orchestration.defaults.driver', 'config');
    }
}
