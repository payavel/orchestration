<?php

namespace Payavel\Orchestration\Traits;

use function Laravel\Prompts\text;

trait AsksQuestions
{
    /**
     * Asks for the name of the entity (provider, account, etc...) to be added.
     *
     * @param string $entity
     * @return string
     */
    protected function askName(string $entity): string
    {
        return text(
            label: "How should the {$this->formatService($entity)} be named?"
        );
    }

    /**
     * Asks for the id of the entity (provider, account, etc...) to be added.
     *
     * @param string $entity
     * @param string $name
     * @return string
     */
    protected function askId(string $entity, string $name): string
    {
        $id = preg_replace('/[^a-z0-9]+/i', '_', strtolower($name));

        return $id === $name
            ? $id
            : text(
                label: "How should the {$this->formatService($entity)} be identified?",
                default: preg_replace('/[^a-z0-9]+/i', '_', strtolower($name))
            );
    }

    /**
     * Properly formats the service entity.
     *
     * @param string $entity
     * @return string
     */
    private function formatService(string $entity): string
    {
        return ($this->serviceConfig ? ($this->serviceConfig->name.' ') : '').$entity;
    }
}
