<?php

namespace Payavel\Serviceable\Tests\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

trait SetUpMode
{
    protected function setUpMode($service = null)
    {
        Config::set(($service ? Str::slug($service->getId()) : 'serviceable') . '.test_mode', $this->fake ?? false);
    }
}
