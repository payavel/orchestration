<?php

namespace Payavel\Orchestration\Tests\Unit\Database;

use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;
use Payavel\Orchestration\Tests\Traits\SetsDatabaseDriver;
use Payavel\Orchestration\Tests\Unit\TestService;

class ServiceTest extends TestService
{
    use CreatesDatabaseServiceables;
    use SetsDatabaseDriver;
}
