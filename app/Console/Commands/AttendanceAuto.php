<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AttendanceAuto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:attendance-auto {--date=} {--mode=presence}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create automatic attendances and mark absent users who did not check activity during the day.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mode = $this->option('mode');

        if ($mode === 'absent') {
            $this->markAbsent();
            return 0;
        }

        if ($mode === 'presence') {
            $this->createMorningPresence();
            return 0;
        }

        $this->error('Mode inconnu. Utilisez --mode=presence ou --mode=absent.');
        return 1;
    }

    protected function createMorningPresence(): void
    {
        $date = Carbon::today()->format('Y-m-d');
        $users = $this->allowedUsers();
        $created = 0;

        foreach ($users as $user) {
            foreach ($user->projects as $project) {
                if (Attendance::where('project_id', $project->id)
                    ->where('user_id', $user->id)
                    ->where('date', $date)
                    ->exists()) {
                    continue;
                }

                Attendance::create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'date' => $date,
                    'expected_arrival' => '08:30',
                    'expected_departure' => '17:00',
                    'break_minutes' => 0,
                    'status' => 'present',
                    'note' => null,
                    'creator_id' => null,
                ]);

                $created++;
            }
        }

        $this->info(sprintf('Présences automatiques créées pour %s en %d enregistrements.', $date, $created));
    }

    protected function markAbsent(): void
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->format('Y-m-d')
            : Carbon::yesterday()->format('Y-m-d');

        $users = $this->allowedUsers();
        $marked = 0;
        $created = 0;

        $activeUserIds = DB::table('tracker_logs')
            ->whereDate('created_at', $date)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        foreach ($users as $user) {
            if (in_array($user->id, $activeUserIds, true)) {
                continue;
            }

            foreach ($user->projects as $project) {
                $attendance = Attendance::where('project_id', $project->id)
                    ->where('user_id', $user->id)
                    ->where('date', $date)
                    ->first();

                if ($attendance) {
                    if (in_array($attendance->status, ['leave', 'vacation', 'congé'], true)) {
                        continue;
                    }
                    if ($attendance->status === 'absent') {
                        continue;
                    }
                    if ($attendance->status === 'pause') {
                        continue;
                    }
                    if ($attendance->arrival_at || $attendance->departure_at) {
                        continue;
                    }

                    $attendance->update([
                        'status' => 'absent',
                        'arrival_at' => null,
                        'departure_at' => null,
                        'break_minutes' => 0,
                    ]);
                    $marked++;
                    continue;
                }

                Attendance::create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'date' => $date,
                    'status' => 'absent',
                    'break_minutes' => 0,
                    'creator_id' => null,
                ]);
                $created++;
            }
        }

        $this->info(sprintf('Absences automatiques traitées pour %s : %d mises à jour, %d créées.', $date, $marked, $created));
    }

    protected function allowedUsers()
    {
        $allowedRoles = ['commercial', 'agent commercial', 'assistant commercial'];

        return User::with('projects')
            ->whereNull('banned_at')
            ->get()
            ->filter(function (User $user) use ($allowedRoles) {
                $role = trim(mb_strtolower((string) $user->role));
                return in_array($role, $allowedRoles, true);
            });
    }
}
