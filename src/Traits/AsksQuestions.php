<?php

namespace Payavel\Orchestration\Traits;

use Illuminate\Support\Str;

use function Laravel\Prompts\text;

trait AsksQuestions
{
    /**
     * Ask for the name of the serviceable entity (provider, merchant, etc...) to be added.
     *
     * @param string $entity
     * @return string
     */
    protected function askName($entity)
    {
        return text(
            label: "What should the {$this->formatService($entity)} be named?"
        );
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
        $id = preg_replace('/[^a-z0-9]+/i', '_', strtolower($name));

        return $id === $name
            ? $id
            : text(
                label: "How should the {$name} {$this->formatService($entity)} be identified?",
                default: preg_replace('/[^a-z0-9]+/i', '_', strtolower($name))
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
