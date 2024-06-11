<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Config;

use Payavel\Orchestration\Tests\Feature\Console\TestOrchestrateProviderCommand;
use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;
use Payavel\Orchestration\Tests\Traits\SetsConfigDriver;

class OrchestrateProviderCommandTest extends TestOrchestrateProviderCommand
{
    use CreatesConfigServiceables;
    use SetsConfigDriver;
}
