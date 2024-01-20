<?php

namespace Payavel\Orchestration\Tests\Unit;

use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;

class FakeConfigServiceTest extends TestService
{
    use CreatesConfigServiceables;

    public $driver = 'config';
    public $fake = true;
}
