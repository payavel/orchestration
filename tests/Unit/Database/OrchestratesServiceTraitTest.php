<?php

namespace Payavel\Orchestration\Tests\Unit\Database;

use Payavel\Orchestration\Tests\Traits\CreatesDatabaseServiceables;
use Payavel\Orchestration\Tests\Traits\SetsDatabaseDriver;
use Payavel\Orchestration\Tests\Unit\TestOrchestratesServiceTrait;

class OrchestratesServiceTraitTest extends TestOrchestratesServiceTrait
{
    use CreatesDatabaseServiceables;
    use SetsDatabaseDriver;
}
