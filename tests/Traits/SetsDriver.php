<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait SetsDriver
{
    protected function setDriver()
    {
        if (
            ! isset($this->driver) ||
            ! method_exists($this, $setDriver = 'set' . Str::Studly($this->driver) . 'Driver')
        ) {
            return;
        }

        $this->$setDriver();
    }

    protected function setConfigDriver()
    {
        Config::set('serviceable.defaults.driver', 'config');
    }

    protected function setDatabaseDriver()
    {
        Config::set('serviceable.defaults.driver', 'database');
    }
}
