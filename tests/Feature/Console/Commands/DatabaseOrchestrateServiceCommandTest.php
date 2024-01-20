<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;

class DatabaseOrchestrateServiceCommandTest extends TestOrchestrateServiceCommand
{
    use CreatesDatabaseServiceables;

    public $driver = 'database';
}
