<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\ImapInboxSync;
use App\Utils\ProjectSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImapSync extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:imap-sync
        {--project= : Synchroniser uniquement ce projet}
        {--force : Synchroniser même si personne n\'a le projet ouvert}';

    /**
     * The console command description.
     */
    protected $description = "Récupère les emails entrants (IMAP) des projets ouverts par au moins un utilisateur";

    /**
     * Minutes of inactivity after which a project is no longer considered open.
     */
    public const OPEN_INTERVAL = 15;

    public function handle(ImapInboxSync $sync): int
    {
        $projectId = $this->option('project');
        $forced = (bool) $this->option('force') || $projectId !== null;

        $projects = Project::query()
            // Only the projects having an email setting can have IMAP.
            ->whereIn('id', function ($query) {
                $query->select('project_id')
                    ->from('project_settings')
                    ->where('key', ImapInboxSync::SETTING);
            })
            ->when($projectId, fn ($query) => $query->where('id', $projectId))
            // The mailbox is only read while someone works on the project:
            // the projects are filtered in one query, not one per project.
            ->when(!$forced, fn ($query) => $query->whereIn('id', $this->openProjectIds()))
            ->orderBy('id')
            ->get();

        Log::info('ImapSync: démarrage', [
            'projects' => $projects->count(),
            'forced' => $forced,
            'project_id' => $projectId,
        ]);

        if ($projects->isEmpty()) {
            Log::info('ImapSync: aucun projet à synchroniser (aucun projet ouvert ou IMAP désactivé)');
        }

        foreach ($projects as $project) {
            $imap = data_get(ProjectSetting::get($project, ImapInboxSync::SETTING), 'imap');

            if (!$imap || !data_get($imap, 'enabled')) {
                Log::debug('ImapSync: projet ignoré (IMAP désactivé)', [
                    'project_id' => $project->id,
                ]);

                continue;
            }

            try {
                $counts = $sync->sync($project);

                Log::info('ImapSync: projet synchronisé', [
                    'project_id' => $project->id,
                ] + $counts);

                $this->line("Projet {$project->id} : {$counts['imported']} email(s) importé(s), {$counts['ignored']} ignoré(s), {$counts['duplicates']} doublon(s).");
            } catch (\Throwable $exception) {
                Log::error('ImapSync: synchronisation impossible', [
                    'project_id' => $project->id,
                    'message' => $exception->getMessage(),
                ]);

                $this->error("Projet {$project->id} : {$exception->getMessage()}");
            }
        }

        Log::info('ImapSync: fin', [
            'projects' => $projects->count(),
            'forced' => $forced,
        ]);

        return self::SUCCESS;
    }

    /**
     * Projects a user is currently working on.
     *
     * users.last_project_id is the project the user last navigated to (it is
     * set by App\Http\Middleware\UserLastProjectCheck, and cleared when the
     * user leaves the project pages); users.last_activity is refreshed on each
     * request by App\Http\Middleware\Authenticate, at most once per minute.
     * A project left open in a tab without any request for fifteen minutes is
     * therefore no longer considered open, and stays unsynchronised.
     */
    protected function openProjectIds()
    {
        return DB::table('users')
            ->select('last_project_id')
            ->whereNotNull('last_project_id')
            ->where('last_activity', '>=', now()->subMinutes(self::OPEN_INTERVAL))
            ->distinct();
    }
}
