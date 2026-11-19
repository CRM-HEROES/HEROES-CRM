<?php

namespace App\Console\Commands;

use App\Agents\Coverage\RouteInventory;
use Illuminate\Console\Command;

class AgentsCoverage extends Command
{
    protected $signature = 'agents:coverage {--gaps : Seulement ce qui manque (sans fiche, sans action)} {--json : Sortie JSON}';

    protected $description = "Ce que le CRM expose (routes de l'API par groupe) contre ce que les agents IA savent (fiches d'aide) et peuvent faire (outils).";

    public function handle(RouteInventory $inventory): int
    {
        $rows = $inventory->matrix();
        $counts = array_count_values(array_map(fn ($row) => $row['status'] ?? 'non classé', $rows));

        if ($this->option('gaps')) {
            $rows = array_values(array_filter($rows, fn ($row) => in_array($row['status'], [
                null, RouteInventory::STATUS_NO_SHEET, RouteInventory::STATUS_NO_ACTION,
            ], true)));
        }

        if ($this->option('json')) {
            $this->line(json_encode(['summary' => $counts, 'groups' => $rows], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->table(
            ['Groupe', 'Fonction', 'Routes (L/É)', 'Fiches', 'Outils écriture', 'Statut', 'Priorité'],
            array_map(fn ($row) => [
                $row['group'],
                $row['label'],
                $row['routes'] . ' (' . $row['read'] . '/' . $row['write'] . ')',
                count($row['sheets']),
                count($row['write_tools']),
                $row['status'] ?? 'NON CLASSÉ',
                $row['priority'] ?? '',
            ], $rows)
        );

        $this->newLine();
        foreach ($counts as $status => $count) {
            $this->line(sprintf('%-14s %d groupe(s)', $status, $count));
        }

        return self::SUCCESS;
    }
}
