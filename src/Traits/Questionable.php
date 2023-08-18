<?php

namespace Payavel\Serviceable\Traits;

use Illuminate\Support\Str;

trait Questionable
{
    /**
     * Ask for the name of the serviceable entity (provider, merchant, etc...) to be added.
     *
     * @param string $entity
     * @return string
     */
    protected function askName($entity)
    {
        return $this->ask("What {$this->formatService($entity)} would you like to add?");
    }

    /**
     * Ask for the id of the serviceable entity (provider, merchant, etc...) to be added.
     *
     * @param string $entity
     * @param string $name
     * @return string
     */
    protected function askId($entity, $name)
    {
        return $this->ask(
            "How would you like to identify the {$name} {$this->formatService($entity)}?",
            preg_replace('/[^a-z0-9]+/i', '_', strtolower($name))
        );
    }

    /**
     * Properly format the service entity.
     *
     * @param string $entity
     * @return string
     */
    private function formatService($entity)
    {
        return ($this->service ? Str::replace('_', ' ', $this->service->getId()) . ' ' : '') . $entity;
    }
}
