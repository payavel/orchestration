<?php

namespace Payavel\Serviceable\Tests\Unit;

class FakeDatabaseServiceTest extends TestService
{
    public $driver = 'database';
    public $fake = true;
}
