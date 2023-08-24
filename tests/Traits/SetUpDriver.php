<?php

namespace Payavel\Serviceable\Tests\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait SetUpDriver
{
    protected function setUpDriver()
    {
        if (
            ! isset($this->driver) ||
            ! method_exists($this, $setUp = 'setUp' . Str::Studly($this->driver))
        ) {
            return;
        }

        $this->$setUp();
    }

    protected function setUpConfig()
    {
        Config::set('serviceable.defaults.driver', 'config');
    }

    protected function setUpDatabase()
    {
        Config::set('serviceable.defaults.driver', 'database');
    }
}
