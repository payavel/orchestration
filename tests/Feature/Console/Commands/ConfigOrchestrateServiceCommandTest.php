<?php

namespace Payavel\Orchestration\Tests\Feature\Console\Commands;

use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;

class ConfigOrchestrateServiceCommandTest extends TestOrchestrateServiceCommand
{
    use CreatesConfigServiceables;

    public $driver = 'config';
}
