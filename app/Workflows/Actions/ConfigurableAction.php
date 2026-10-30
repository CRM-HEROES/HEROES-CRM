<?php

namespace App\Workflows\Actions;

/**
 * An action that checks its own configuration when the workflow is
 * published or activated.
 */
interface ConfigurableAction
{
    /**
     * @param  int|null  $projectId  null: only the structure is checked, not the environment
     * @param  string  $label  name of the node, for the messages
     * @return string[] problems, in plain language
     */
    public function validateConfig(array $config, ?int $projectId, string $label): array;
}
