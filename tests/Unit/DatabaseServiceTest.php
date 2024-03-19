<?php

namespace Payavel\Orchestration\Tests\Unit;

use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;
use Payavel\Orchestration\Tests\Traits\SetsDatabaseDriver;

class DatabaseServiceTest extends TestService
{
    use CreatesDatabaseServiceables;
    use SetsDatabaseDriver;
}
