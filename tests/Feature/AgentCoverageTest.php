<?php

namespace Tests\Feature;

use App\Agents\Coverage\RouteInventory;
use App\Agents\Tools\ToolRegistry;
use Tests\TestCase;

/**
 * Everything the CRM exposes is classified for the AI agents: documented, usable by a tool, or excluded on purpose.
 * A new route group without a line in resources/agent-coverage.json fails here, so nothing is forgotten silently.
 */
class AgentCoverageTest extends TestCase
{
    protected function inventory(): RouteInventory
    {
        return app(RouteInventory::class);
    }

    public function test_every_group_of_routes_of_the_api_is_classified(): void
    {
        $unclassified = array_values(array_diff(
            array_keys($this->inventory()->groups()),
            array_keys($this->inventory()->classification())
        ));

        $this->assertSame([], $unclassified, 'Groupes de routes à classer dans resources/agent-coverage.json (php artisan agents:coverage)');
    }

    public function test_the_classification_has_no_group_that_does_not_exist_any_more(): void
    {
        $obsolete = array_values(array_diff(
            array_keys($this->inventory()->classification()),
            array_keys($this->inventory()->groups())
        ));

        $this->assertSame([], $obsolete, 'Groupes classés mais sans route');
    }

    public function test_sheets_and_tools_named_by_the_classification_exist(): void
    {
        $tools = app(ToolRegistry::class);
        $problems = [];

        foreach ($this->inventory()->classification() as $group => $entry) {
            foreach ($entry['sheets'] as $sheet) {
                if (!is_file(base_path("resources/agent-knowledge/{$sheet}.md"))) {
                    $problems[] = "$group : fiche inconnue $sheet";
                }
            }
            foreach (array_merge($entry['read_tools'], $entry['write_tools']) as $tool) {
                if (!$tools->has($tool)) {
                    $problems[] = "$group : outil inconnu $tool";
                }
            }
            if (isset($entry['excluded']) && trim($entry['excluded']) === '') {
                $problems[] = "$group : une exclusion sans raison";
            }
            if (isset($entry['priority']) && !in_array($entry['priority'], ['haute', 'moyenne'], true)) {
                $problems[] = "$group : priorité inconnue {$entry['priority']}";
            }
        }

        $this->assertSame([], $problems);
    }

    public function test_every_group_to_cover_has_at_least_one_help_sheet_except_the_ones_excluded(): void
    {
        $withoutSheet = [];

        foreach ($this->inventory()->matrix() as $row) {
            if ($row['status'] === RouteInventory::STATUS_NO_SHEET) {
                $withoutSheet[] = $row['group'];
            }
        }

        $this->assertSame([], $withoutSheet, 'Fonctions du CRM sans fiche d\'aide : écrire la fiche ou exclure le groupe avec une raison');
    }

    public function test_every_write_tool_is_used_by_a_group_so_none_is_forgotten(): void
    {
        $classified = [];
        foreach ($this->inventory()->classification() as $entry) {
            $classified = array_merge($classified, $entry['read_tools'], $entry['write_tools']);
        }

        $unplaced = array_values(array_diff(
            array_map(fn ($tool) => $tool->key(), app(ToolRegistry::class)->all()),
            $classified,
            // Tools that are about the agent itself or about the documentation, not a group of routes
            ['app_help', 'agent_docs', 'call_transfer', 'prospect_history']
        ));

        $this->assertSame([], $unplaced, 'Outils des agents absents de resources/agent-coverage.json');
    }

    public function test_every_tool_has_a_french_label_for_the_chat(): void
    {
        $labels = json_decode(file_get_contents(base_path('resources/js/lang/fr.json')), true)['agent']['tools'] ?? [];
        $missing = array_values(array_diff(
            array_map(fn ($tool) => $tool->key(), app(ToolRegistry::class)->all()),
            array_keys($labels)
        ));

        $this->assertSame([], $missing, 'Outils sans libellé dans fr.json (agent.tools) : le chat afficherait la clé brute');
    }

    public function test_every_tool_named_by_a_skill_exists(): void
    {
        $tools = app(ToolRegistry::class);
        $unknown = [];

        foreach (config('agents.skills') as $skill => $definition) {
            foreach ($definition['tools'] as $tool) {
                if (!$tools->has($tool)) {
                    $unknown[] = "$skill : $tool";
                }
            }
        }

        $this->assertSame([], $unknown, 'Compétences qui citent un outil inexistant');
    }
}
