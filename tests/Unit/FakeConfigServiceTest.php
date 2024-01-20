<?php

namespace Payavel\Orchestration\Tests\Unit;

use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;
use Payavel\Orchestration\Tests\Traits\SetsConfigDriver;

class FakeConfigServiceTest extends TestService
{
    use CreatesConfigServiceables,
        SetsConfigDriver;

    public $fake = true;
}
