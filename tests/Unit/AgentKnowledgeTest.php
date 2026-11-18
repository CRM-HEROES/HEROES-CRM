<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * The help sheets of the AI agents must not drift from the interface they describe.
 */
class AgentKnowledgeTest extends TestCase
{
    protected function sheets(): array
    {
        return glob(dirname(__DIR__, 2) . '/resources/agent-knowledge/*.md') ?: [];
    }

    public function test_every_sheet_has_the_standard_sections(): void
    {
        $this->assertNotEmpty($this->sheets());

        foreach ($this->sheets() as $sheet) {
            $text = file_get_contents($sheet);
            foreach (['# ', '## À quoi ça sert', '## Comment faire', '## Règles', '## Limites', '## Dans l\'interface'] as $title) {
                $this->assertStringContainsString($title, $text, basename($sheet) . ' : ' . $title);
            }
        }
    }

    public function test_interface_files_quoted_by_the_sheets_exist(): void
    {
        $root = dirname(__DIR__, 2) . '/resources/js/';

        foreach ($this->sheets() as $sheet) {
            preg_match_all('/`((?:components|apis|store)\/[^`{}\s]+)`/', file_get_contents($sheet), $matches);
            foreach (array_unique($matches[1]) as $path) {
                $this->assertFileExists($root . $path, basename($sheet) . ' cite un fichier inexistant : ' . $path);
            }
        }
    }

    public function test_labels_quoted_by_the_sheets_exist_in_the_interface(): void
    {
        $root = dirname(__DIR__, 2) . '/resources/js';
        $haystack = file_get_contents($root . '/lang/fr.json');
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (in_array($file->getExtension(), ['vue', 'js'], true)) {
                $haystack .= file_get_contents($file->getPathname());
            }
        }
        // The menus of the server are translated by Laravel, not by fr.json
        foreach (glob(dirname(__DIR__, 2) . '/resources/lang/fr/*.php') ?: [] as $translation) {
            $haystack .= file_get_contents($translation);
        }
        // A quote escaped in a JavaScript string is still the same label
        $haystack = str_replace("\'", "'", $haystack);
        // Technical words and agent-facing names are not interface labels
        $ignored = ['⋯', 'skipped'];

        $missing = [];
        foreach ($this->sheets() as $sheet) {
            preg_match_all('/«\s*([^»]{2,60}?)\s*»/u', file_get_contents($sheet), $matches);
            foreach (array_unique($matches[1]) as $label) {
                // The sheets write typographic apostrophes; the interface may use straight ones
                $variants = [$label, str_replace('’', "'", $label)];
                $found = false;
                foreach ($variants as $variant) {
                    $found = $found || str_contains($haystack, $variant);
                }
                if (!in_array(preg_replace('/[\s\x{00A0}\x{202F}]+/u', '', $label), $ignored, true) && !$found) {
                    $missing[] = basename($sheet) . ' : « ' . $label . ' »';
                }
            }
        }

        $this->assertSame([], $missing, "Libellés cités par les fiches mais absents de l'interface");
    }

    public function test_rights_quoted_by_the_sheets_exist_in_the_list_of_permissions(): void
    {
        $catalogue = file_get_contents(dirname(__DIR__, 2) . '/resources/agent-knowledge/droits-permissions.md');
        // The list of the permission screen, and the rights the CRM checks without a box of their own
        $list = substr($catalogue, (int) strpos($catalogue, '# Liste des droits'));
        preg_match('/sans case dédiée dans l’écran des permissions :(.*?)\. On ne peut pas/s', $catalogue, $withoutBox);
        preg_match_all('/`(all(?:\.[a-z0-9-]+)*)`/', $list . ($withoutBox[1] ?? ''), $known);
        $known = array_unique($known[1]);

        $unknown = [];
        foreach ($this->sheets() as $sheet) {
            if (basename($sheet) === 'droits-permissions.md') {
                continue;
            }
            preg_match_all('/`(all(?:\.[a-z0-9-]+)+)`/', file_get_contents($sheet), $matches);
            foreach (array_unique($matches[1]) as $right) {
                // A right of the list, or a group of rights of the list (all.prospect)
                $isGroup = (bool) array_filter($known, fn ($candidate) => str_starts_with($candidate, $right . '.'));
                if (!in_array($right, $known, true) && !$isGroup) {
                    $unknown[] = basename($sheet) . ' : ' . $right;
                }
            }
        }

        $this->assertSame([], $unknown, 'Droits cités par les fiches mais absents de la liste des permissions');
    }

    public function test_topics_named_by_the_sheets_exist(): void
    {
        $topics = array_map(fn ($sheet) => basename($sheet, '.md'), $this->sheets());
        $missing = [];

        foreach ($this->sheets() as $sheet) {
            preg_match_all('/sujets? `([a-z0-9-]+)`/', file_get_contents($sheet), $matches);
            foreach ($matches[1] as $topic) {
                if (!in_array($topic, $topics, true)) {
                    $missing[] = basename($sheet) . ' : ' . $topic;
                }
            }
        }

        $this->assertSame([], $missing, 'Sujets cités par les fiches mais inexistants');
    }

    public function test_every_sheet_starts_its_purpose_with_a_sentence_the_list_can_summarise(): void
    {
        foreach ($this->sheets() as $sheet) {
            $this->assertMatchesRegularExpression('/^## À quoi ça sert\R\S/mu', file_get_contents($sheet), basename($sheet));
        }
    }

    public function test_sheets_do_not_promise_behaviour_the_code_does_not_have(): void
    {
        foreach ($this->sheets() as $sheet) {
            $text = file_get_contents($sheet);
            $this->assertStringNotContainsString('différée sur la file', $text, basename($sheet));
        }
    }
}
