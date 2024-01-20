<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;

class ConfigOrchestrateProviderCommandTest extends TestOrchestrateProviderCommand
{
    use CreatesConfigServiceables;

    public $driver = 'config';
}
