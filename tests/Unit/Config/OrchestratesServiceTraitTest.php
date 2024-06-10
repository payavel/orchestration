<?php

namespace Payavel\Orchestration\Tests\Unit\Config;

use Payavel\Orchestration\Tests\Traits\CreatesConfigServiceables;
use Payavel\Orchestration\Tests\Traits\SetsConfigDriver;
use Payavel\Orchestration\Tests\Unit\TestOrchestratesServiceTrait;

class OrchestratesServiceTraitTest extends TestOrchestratesServiceTrait
{
    use CreatesConfigServiceables;
    use SetsConfigDriver;
}
