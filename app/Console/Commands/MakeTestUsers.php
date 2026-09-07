<?php

namespace App\Console\Commands;

use App\Models\Calendar;
use App\Models\Event;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Generates a batch of test user accounts attached to a project, so testing
 * lead assignment (pick them in an import's "Relations" step, check who
 * receives the leads) doesn't require creating fake agents by hand every
 * time. A few of them are put in an ongoing "Off" event on purpose, to
 * verify they still get assigned leads now that the availability filter is
 * paused (see ProspectAutoAssignment::$enforceAvailabilityFilter). All
 * users created this way share the @test.heroes-crm.local email domain, so
 * they can be found and wiped in bulk with --clean.
 */
class MakeTestUsers extends Command
{
    protected $signature = 'app:make-test-users {project?} {--count=5 : How many test users to create} {--busy=2 : How many of them to mark as currently unavailable (ongoing "Off" event)} {--clean : Delete every user previously created by this command instead of creating new ones}';
    protected $description = 'Create (or clean up) test user accounts for manually testing lead assignment.';

    const EMAIL_DOMAIN = 'test.heroes-crm.local';
    const TEST_PASSWORD = 'Test1234!';

    public function handle()
    {
        if ($this->option('clean')) {
            return $this->clean();
        }

        $projectArg = $this->argument('project');
        $project = $projectArg
            ? (Project::find($projectArg) ?: Project::where('slug', $projectArg)->first())
            : Project::first();

        if (!$project) {
            $this->error($projectArg ? "Projet introuvable : {$projectArg}" : "Aucun projet en base.");
            return self::FAILURE;
        }

        $count = max(1, (int) $this->option('count'));
        $busyCount = max(0, min($count, (int) $this->option('busy')));

        $faker = \Faker\Factory::create('fr_FR');
        $offCalendar = $this->getOrCreateOffCalendar($project);

        $rows = [];

        // withoutEvents: skips UserObserver::created(), which sends a real
        // "welcome" notification (email) on every User::create() — not
        // wanted for throwaway test accounts, and would otherwise try to
        // hit the configured mail server for each one.
        User::withoutEvents(function () use ($faker, $project, $offCalendar, $count, $busyCount, &$rows) {
            for ($i = 1; $i <= $count; $i++) {
                $firstName = $faker->firstName();
                $lastName = $faker->lastName();
                $email = Str::slug($firstName . '.' . $lastName, '.') . '.' . $i . '@' . self::EMAIL_DOMAIN;

                $user = User::create([
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $email,
                    'password' => Hash::make(self::TEST_PASSWORD),
                    'phone_number' => $faker->phoneNumber(),
                ]);

                $project->users()->syncWithoutDetaching([$user->id]);

                $isBusy = $i <= $busyCount;

                if ($isBusy) {
                    Event::create([
                        'calendar_id' => $offCalendar->id,
                        'user_id' => $user->id,
                        'name' => 'Test - indisponible',
                        'started_at' => now()->subHour(),
                        'ended_at' => now()->addDays(2),
                    ]);
                }

                $rows[] = [$user->id, $user->name, $email, self::TEST_PASSWORD, $isBusy ? 'Oui (Off, 2 jours)' : 'Non'];
            }
        });

        $this->info("{$count} utilisateur(s) de test créé(s) pour le projet {$project->name} (#{$project->id}).");
        $this->table(['ID', 'Nom', 'Email', 'Mot de passe', 'Marqué occupé'], $rows);
        $this->line('Sélectionnez-les dans l\'étape "Relations" d\'un import pour tester l\'affectation.');
        $this->line('Pour les supprimer : php artisan app:make-test-users --clean');

        return self::SUCCESS;
    }

    protected function getOrCreateOffCalendar(Project $project): Calendar
    {
        return $project->calendars()->firstOrCreate(
            ['name' => 'Off'],
            ['color' => '#999999', 'bgcolor' => '#CCCCCC']
        );
    }

    protected function clean()
    {
        $ids = User::where('email', 'like', '%@' . self::EMAIL_DOMAIN)->pluck('id');

        if ($ids->isEmpty()) {
            $this->info('Aucun utilisateur de test à nettoyer.');
            return self::SUCCESS;
        }

        Event::whereIn('user_id', $ids)->delete();
        DB::table('user_project')->whereIn('user_id', $ids)->delete();
        User::whereIn('id', $ids)->delete();

        $this->info(count($ids) . ' utilisateur(s) de test supprimé(s) : ' . $ids->implode(', '));

        return self::SUCCESS;
    }
}
