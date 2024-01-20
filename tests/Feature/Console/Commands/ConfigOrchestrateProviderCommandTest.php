<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;
use Payavel\Orchestration\Tests\Traits\SetsConfigDriver;

class ConfigOrchestrateProviderCommandTest extends TestOrchestrateProviderCommand
{
    use CreatesConfigServiceables,
        SetsConfigDriver;
}
