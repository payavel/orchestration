<?php

namespace Payavel\Orchestration\Tests\Unit;

class FakeDatabaseServiceTest extends TestService
{
    public $driver = 'database';
    public $fake = true;
}
