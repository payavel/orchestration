<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

trait SetsDatabaseDriver
{
    /**
     * Set the driver.
     *
     * @return void
     */
    protected function setDriver()
    {
        Config::set('orchestration.defaults.driver', 'database');
    }
}
