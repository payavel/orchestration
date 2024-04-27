<?php

namespace Payavel\Orchestration\Traits;

use RuntimeException;

trait ThrowsRuntimeException
{
    /**
     * Request the developer to implement the specified method in order to get the expected result.
     *
     * @param string $method
     *
     * @throws \RuntimeException
     */
    private function throwRuntimeException($method)
    {
        throw new RuntimeException(get_class($this)."::class does not implement the {$method}() method.");
    }
}
