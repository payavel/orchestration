<?php

namespace Payavel\Orchestration\Tests\Unit;

use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;

class FakeDatabaseServiceTest extends TestService
{
    use CreatesDatabaseServiceables;
    
    public $driver = 'database';
    public $fake = true;
}
