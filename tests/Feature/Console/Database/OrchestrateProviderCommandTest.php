<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Database;

use Payavel\Orchestration\Tests\Feature\Console\TestOrchestrateProviderCommand;
use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;
use Payavel\Orchestration\Tests\Traits\SetsDatabaseDriver;

class OrchestrateProviderCommandTest extends TestOrchestrateProviderCommand
{
    use CreatesDatabaseServiceables;
    use SetsDatabaseDriver;
}
