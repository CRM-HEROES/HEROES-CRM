<?php

namespace App\Agents;

use App\Agents\Tools\AgentToolInterface;
use App\Models\Agent;

/**
 * Decides whether an agent may call a tool.
 *
 * The effective rights of an agent are the rights its owner has in the
 * project (the CRM ability the tool declares, checked live by the caller)
 * INTERSECTED with what was granted to the agent. An agent can never do more
 * than its owner, and never changes its own grants: no tool touches them.
 *
 * It is a pure decision (no query): the caller gives the facts, so the same
 * rules apply to a chat turn, a schedule and a workflow.
 */
class PermissionGate
{
    /**
     * @param  string[]  $allowedToolKeys  tools of the skills of the agent
     * @param  array<string, string>  $grants  tool key => "allow" | "deny"
     * @param  bool|null  $abilityGranted  does the owner have the ability the tool declares?
     *                                     (null: the tool declares none)
     */
    public function decide(
        AgentToolInterface $tool,
        string $autonomy,
        array $allowedToolKeys,
        array $grants,
        ?bool $abilityGranted,
    ): PermissionDecision {
        $key = $tool->key();

        // An explicit deny always wins over the skills
        if (($grants[$key] ?? null) === 'deny') {
            return PermissionDecision::deny("L'outil \"$key\" est désactivé pour cet agent.");
        }

        $granted = ($grants[$key] ?? null) === 'allow' || in_array($key, $allowedToolKeys, true);

        if (!$granted) {
            return PermissionDecision::deny("L'outil \"$key\" n'est pas accordé à cet agent.");
        }

        // The CRM is the only authority on the rights of the owner
        if ($tool->ability() !== null && $abilityGranted !== true) {
            return PermissionDecision::deny("L'utilisateur propriétaire de l'agent n'a pas le droit \"{$tool->ability()}\" dans ce projet.");
        }

        $risk = $tool->risk();

        if (!in_array($risk, AgentToolInterface::RISKS, true)) {
            return PermissionDecision::deny("Le niveau de risque de l'outil \"$key\" est inconnu.");
        }

        if ($risk === AgentToolInterface::READ) {
            return PermissionDecision::allow();
        }

        // From here the tool changes something
        if ($autonomy === Agent::READ_ONLY || !in_array($autonomy, Agent::AUTONOMIES, true)) {
            return PermissionDecision::deny("Cet agent est en lecture seule : il ne peut pas utiliser \"$key\".");
        }

        if ($autonomy === Agent::PROPOSE) {
            return PermissionDecision::approve('Cet agent propose ses modifications : elles attendent votre validation.');
        }

        // Auto: some risks are always decided by a human
        if (in_array($risk, config('agents.approval_required_risks', []), true)) {
            return PermissionDecision::approve("Les actions de type \"$risk\" sont toujours validées par un humain.");
        }

        return PermissionDecision::allow();
    }
}
