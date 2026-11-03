<?php

namespace App\Agents;

/**
 * Skills declared in config/agents.php: tools plus the way to use them.
 */
class SkillRegistry
{
    /**
     * @return array<string, array{label: string, tools: string[], playbook: string}>
     */
    public function all(): array
    {
        return config('agents.skills', []);
    }

    public function has(string $key): bool
    {
        return isset($this->all()[$key]);
    }

    /**
     * Tool keys of the skills (the unknown skills are ignored)
     *
     * @param  string[]  $skillKeys
     * @return string[]
     */
    public function toolKeys(array $skillKeys): array
    {
        $keys = [];

        foreach ($skillKeys as $skill) {
            foreach ($this->all()[$skill]['tools'] ?? [] as $tool) {
                $keys[$tool] = true;
            }
        }

        return array_keys($keys);
    }

    /**
     * Playbooks of the skills, in the order of the keys
     *
     * @param  string[]  $skillKeys
     * @return string[]
     */
    public function playbooks(array $skillKeys): array
    {
        $playbooks = [];

        foreach ($skillKeys as $skill) {
            if (!empty($this->all()[$skill]['playbook'])) {
                $playbooks[] = $this->all()[$skill]['playbook'];
            }
        }

        return $playbooks;
    }
}
