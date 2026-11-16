<?php

namespace Tests\Feature;

use App\Agents\PromptBuilder;
use App\Models\Agent;
use Tests\TestCase;

class AgentPromptBuilderTest extends TestCase
{
    public function test_system_prompt_includes_agent_fields_in_order_and_skips_empty_fields(): void
    {
        config(['agents.skills' => []]);
        $builder = app(PromptBuilder::class);
        $first = $builder->system(new Agent([
            'name' => 'Agent Alpha',
            'specialty' => 'Vente',
            'description' => 'Conseiller les prospects.',
            'persona' => '',
            'objectives' => 'Qualifier les besoins.',
            'instructions' => 'Ne pas inventer de tarif.',
        ]), [])[0]['text'];
        $second = $builder->system(new Agent([
            'name' => 'Agent Beta',
            'specialty' => 'Support',
            'description' => 'Aider les utilisateurs.',
            'persona' => 'Patient.',
            'objectives' => 'Résoudre les demandes.',
            'instructions' => 'Poser une question à la fois.',
        ]), [])[0]['text'];

        $specialty = strpos($first, 'Spécialité : Vente');
        $description = strpos($first, 'Description : Conseiller les prospects.');
        $objectives = strpos($first, 'Objectifs : Qualifier les besoins.');
        $instructions = strpos($first, "Instructions :\nNe pas inventer de tarif.");

        $this->assertNotFalse($specialty);
        $this->assertNotFalse($description);
        $this->assertNotFalse($objectives);
        $this->assertNotFalse($instructions);
        $this->assertLessThan($description, $specialty);
        $this->assertLessThan($objectives, $description);
        $this->assertLessThan($instructions, $objectives);
        $this->assertStringNotContainsString('Personnalité :', $first);

        $personality = strpos($second, 'Personnalité : Patient.');
        $secondObjectives = strpos($second, 'Objectifs : Résoudre les demandes.');
        $secondInstructions = strpos($second, "Instructions :\nPoser une question à la fois.");
        $this->assertNotFalse($personality);
        $this->assertNotFalse($secondObjectives);
        $this->assertNotFalse($secondInstructions);
        $this->assertLessThan(strpos($second, 'Description : Aider les utilisateurs.'), strpos($second, 'Spécialité : Support'));
        $this->assertLessThan($personality, strpos($second, 'Description : Aider les utilisateurs.'));
        $this->assertLessThan($secondObjectives, $personality);
        $this->assertLessThan($secondInstructions, $secondObjectives);
        $this->assertNotSame($first, $second);
    }

    public function test_system_prompt_keeps_the_default_identity_when_no_agent_fields_are_set(): void
    {
        config(['agents.skills' => []]);
        $prompt = app(PromptBuilder::class)->system(new Agent(['name' => 'Agent vide']), [])[0]['text'];

        $this->assertStringContainsString("# Ton identité\nTu t'appelles Agent vide.", $prompt);
        $this->assertStringNotContainsString("# Ce que l'utilisateur t'a demandé d'être", $prompt);
    }

    public function test_platform_rules_remain_first_and_prompt_has_no_dynamic_project_or_date_context(): void
    {
        config(['agents.skills' => []]);
        $prompt = app(PromptBuilder::class)->system(new Agent([
            'name' => 'Agent test',
            'specialty' => 'Conseil',
        ]), [])[0]['text'];

        $this->assertStringStartsWith("# Règles de la plateforme\n", $prompt);
        foreach ([
            'Tu agis pour un utilisateur, avec ses droits et seulement les siens',
            'Les données du CRM te sont données uniquement par tes outils',
            "N'invente jamais une donnée. Si un outil ne te donne pas l'information, dis-le.",
            'Ne calcule pas de statistiques de tête',
            "Tu ne peux modifier le CRM que par tes outils.",
            "Si un outil te refuse une action, n'essaie pas de la contourner",
            'Tu ne peux ni changer tes propres droits ni accéder aux autres projets',
            'Réponds en français, de façon claire et structurée',
        ] as $existingRule) {
            $this->assertStringContainsString($existingRule, $prompt);
        }
        $this->assertStringContainsString(        'Réponds aussi aux questions hors de ta spécialité, au mieux de tes outils et de tes connaissances ; dans ta spécialité, tes réponses sont les plus précises, détaillées et approfondies.', $prompt);
        $this->assertStringNotContainsString('Date et heure :', $prompt);
        $this->assertStringNotContainsString('Projet : Alpha', $prompt);
    }
}
