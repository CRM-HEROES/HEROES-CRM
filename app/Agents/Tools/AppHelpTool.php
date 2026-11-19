<?php

namespace App\Agents\Tools;

use App\Agents\AgentExecutionContext;

/**
 * Returns the checked-in CRM feature guides without accepting file paths.
 */
class AppHelpTool implements AgentToolInterface
{
    public function key(): string
    {
        return 'app_help';
    }

    public function description(): string
    {
        return "Documentation des fonctionnalités du CRM, basée sur le code de l'application. Sans topic : liste les sujets. Avec topic : renvoie la fiche ou une section. À utiliser pour répondre aux questions sur le fonctionnement; ne complète pas avec des informations non présentes dans le résultat.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'topic' => [
                    'type' => 'string',
                    'enum' => array_keys($this->topics() ?? []),
                    'description' => 'Sujet documenté (omis pour afficher la liste).',
                ],
                'section' => [
                    'type' => 'string',
                    'maxLength' => 100,
                    'description' => 'Titre exact d’une section ## de la fiche.',
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function module(): string
    {
        return 'help';
    }

    public function risk(): string
    {
        return self::READ;
    }

    public function ability(): ?string
    {
        return null;
    }

    public function handle(AgentExecutionContext $context, array $input): ToolResult
    {
        $topics = $this->topics();
        if ($topics === null) {
            return ToolResult::error('Les fiches d’aide sont indisponibles.', ToolResult::UNAVAILABLE);
        }

        if (!isset($input['topic'])) {
            $titles = [];
            $summaries = [];

            foreach ($topics as $topic => $file) {
                $content = $this->readTopic($topic, $file);

                if ($content instanceof ToolResult) {
                    return $content;
                }

                $titles[$topic] = $this->title($content);
                $summaries[$topic] = $this->summary($content);
            }

            // The summaries tell what each sheet covers, to open the right one when the first is not enough
            return ToolResult::ok(['topics' => $titles, 'summaries' => $summaries], 'Liste des sujets d’aide', count($titles));
        }

        $topic = $input['topic'];
        if (!isset($topics[$topic])) {
            return ToolResult::error('Sujet d’aide inconnu.', ToolResult::NOT_FOUND);
        }

        $content = $this->readTopic($topic, $topics[$topic]);
        if ($content instanceof ToolResult) {
            return $content;
        }

        if (isset($input['section'])) {
            $content = $this->section($content, $input['section']);

            if ($content === null) {
                return ToolResult::error('Section inconnue pour ce sujet.', ToolResult::NOT_FOUND);
            }
        }

        $limit = max(0, (int) config('agents.max_tool_result_chars', 30000) - 512);
        if (!isset($input['section']) && mb_strlen($content) > $limit) {
            $content = $this->sectionIndex($content);
        } elseif (mb_strlen($content) > $limit) {
            $content = mb_substr($content, 0, $limit);
        }

        return ToolResult::ok(['topic' => $topic, 'content' => $content], "Aide CRM : $topic", 1);
    }

    public function preview(AgentExecutionContext $context, array $input): array
    {
        return [];
    }

    protected function topics(): ?array
    {
        $directory = base_path('resources/agent-knowledge');
        $realDirectory = realpath($directory);
        $files = $realDirectory === false ? false : scandir($realDirectory);
        if ($files === false) {
            return null;
        }

        $topics = [];
        foreach ($files as $file) {
            if (!preg_match('/\A([a-z0-9-]+)\.md\z/', $file, $matches)) {
                continue;
            }

            $path = $realDirectory . DIRECTORY_SEPARATOR . $file;
            $realPath = realpath($path);
            if ($realPath === false || dirname($realPath) !== $realDirectory || !is_file($realPath)) {
                continue;
            }

            $topics[$matches[1]] = $file;
        }

        ksort($topics);

        return $topics;
    }

    protected function readTopic(string $topic, string $file): string|ToolResult
    {
        if (!preg_match('/\A[a-z0-9-]+\.md\z/', $file)) {
            return ToolResult::error("La fiche d’aide \"$topic\" est indisponible.", ToolResult::UNAVAILABLE);
        }

        $directory = realpath(base_path('resources/agent-knowledge'));
        $path = $directory === false ? false : realpath($directory . DIRECTORY_SEPARATOR . $file);
        if ($directory === false || $path === false || dirname($path) !== $directory || !is_file($path)) {
            return ToolResult::error("La fiche d’aide \"$topic\" est indisponible.", ToolResult::UNAVAILABLE);
        }

        $content = is_file($path) ? file_get_contents($path) : false;

        if ($content === false) {
            return ToolResult::error("La fiche d’aide \"$topic\" est indisponible.", ToolResult::UNAVAILABLE);
        }

        return $content;
    }

    protected function title(string $content): string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return 'Titre indisponible';
    }

    /**
     * What the sheet is for: the first sentences of its "À quoi ça sert" section, shortened.
     */
    protected function summary(string $content): string
    {
        if (!preg_match('/^##\s+À quoi ça sert\s*\R(.+?)(?:\R\R|\z)/msu', $content, $matches)) {
            return '';
        }

        return mb_substr(trim(preg_replace('/\s+/u', ' ', $matches[1])), 0, 220);
    }

    protected function sectionIndex(string $content): string
    {
        preg_match_all('/^##\s+(.+?)\s*$/mu', $content, $matches);

        return "Cette fiche est longue. Demandez une section en passant son titre exact.\n\n"
            . implode("\n", array_map(fn (string $title) => '- ' . $title, $matches[1]));
    }

    protected function section(string $content, string $section): ?string
    {
        $lines = preg_split('/\R/u', $content);
        $wanted = mb_strtolower(trim(preg_replace('/^##\s*/', '', $section)));
        $result = [];
        $matched = false;

        foreach ($lines as $line) {
            if (preg_match('/^##\s+(.+?)\s*$/u', $line, $matches)) {
                if ($matched) {
                    break;
                }

                $matched = mb_strtolower(trim($matches[1])) === $wanted;
            }

            if ($matched) {
                $result[] = $line;
            }
        }

        return $matched ? implode("\n", $result) : null;
    }
}
