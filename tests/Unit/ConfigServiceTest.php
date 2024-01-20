<?php

namespace Payavel\Orchestration\Tests\Unit;

use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;

class ConfigServiceTest extends TestService
{
    use CreatesConfigServiceables;

    public $driver = 'config';
}
