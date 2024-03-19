<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;
use Payavel\Orchestration\Tests\Traits\SetsDatabaseDriver;

class DatabaseOrchestrateProviderCommandTest extends TestOrchestrateProviderCommand
{
    use CreatesDatabaseServiceables;
    use SetsDatabaseDriver;
}
