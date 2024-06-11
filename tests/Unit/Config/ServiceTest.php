<?php

namespace Payavel\Orchestration\Tests\Unit\Config;

use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;
use Payavel\Orchestration\Tests\Traits\SetsConfigDriver;
use Payavel\Orchestration\Tests\Unit\TestService;

class ServiceTest extends TestService
{
    use CreatesConfigServiceables;
    use SetsConfigDriver;
}
