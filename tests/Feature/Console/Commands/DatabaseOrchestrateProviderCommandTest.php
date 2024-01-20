<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;

class DatabaseOrchestrateProviderCommandTest extends TestOrchestrateProviderCommand
{
    use CreatesDatabaseServiceables;

    public $driver = 'database';
}
