<?php

namespace Payavel\Orchestration\Tests\Unit;

use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;

class DatabaseServiceTest extends TestService
{
    use CreatesDatabaseServiceables;

    public $driver = 'database';
}
