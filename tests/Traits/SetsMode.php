<?php

namespace Payavel\Orchestration\Tests\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait SetsMode
{
    protected function setMode($service = null)
    {
        Config::set(($service ? Str::slug($service->getId()) : 'serviceable') . '.test_mode', $this->fake ?? false);
    }
}
