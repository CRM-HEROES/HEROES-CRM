<?php

namespace App\Http\Controllers\API\Project;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Liste de tous les utilisateurs (employés à gérer dans le planning).
     */
    public function users(Project $project)
    {
        return User::orderBy('name')->get(['id', 'name', 'last_name', 'email']);
    }

    /**
     * Liste des pointages du projet sur une période (ex: une semaine).
     */
    public function index(Request $request, Project $project)
    {
        $this->validate($request, [
            'from' => 'sometimes|date',
            'to'   => 'sometimes|date',
        ]);

        return Attendance::where('project_id', $project->id)
            ->when($request->filled('from'), function ($query) use ($request) {
                $query->whereDate('date', '>=', $request->input('from'));
            })
            ->when($request->filled('to'), function ($query) use ($request) {
                $query->whereDate('date', '<=', $request->input('to'));
            })
            ->get();
    }

    /**
     * Créer ou mettre à jour le pointage d'un employé pour une date.
     */
    public function store(Request $request, Project $project)
    {
        $data = $this->validate($request, [
            'user_id'            => 'required|exists:users,id',
            'date'               => 'required|date',
            'expected_arrival'   => 'nullable|date_format:H:i',
            'expected_departure' => 'nullable|date_format:H:i',
            'arrival_at'         => 'nullable|date',
            'departure_at'       => 'nullable|date',
            'break_minutes'      => 'nullable|integer|min:0',
            'status'             => 'nullable|string',
            'note'               => 'nullable|string',
        ]);

        $attendance = Attendance::updateOrCreate(
            [
                'project_id' => $project->id,
                'user_id'    => $data['user_id'],
                'date'       => $data['date'],
            ],
            array_merge($data, ['creator_id' => auth()->id()])
        );

        return $attendance->refresh();
    }

    /**
     * Poser un congé sur une période : crée un pointage "congé"
     * pour chaque jour entre `from` et `to` (inclus).
     */
    public function leave(Request $request, Project $project)
    {
        $data = $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'from'    => 'required|date',
            'to'      => 'required|date|after_or_equal:from',
            'note'    => 'nullable|string',
        ]);

        $created = [];

        foreach (CarbonPeriod::create($data['from'], $data['to']) as $day) {
            $created[] = Attendance::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'user_id'    => $data['user_id'],
                    'date'       => $day->format('Y-m-d'),
                ],
                [
                    'status'       => 'leave',
                    'note'         => $data['note'] ?? null,
                    'arrival_at'   => null,
                    'departure_at' => null,
                    'creator_id'   => auth()->id(),
                ]
            )->refresh();
        }

        return $created;
    }

    /**
     * Annuler un congé sur une période : supprime les pointages "congé"
     * de l'employé entre `from` et `to` (inclus).
     */
    public function removeLeave(Request $request, Project $project)
    {
        $data = $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'from'    => 'required|date',
            'to'      => 'required|date|after_or_equal:from',
        ]);

        Attendance::where('project_id', $project->id)
            ->where('user_id', $data['user_id'])
            ->where('status', 'leave')
            ->whereBetween('date', [$data['from'], $data['to']])
            ->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }

    /**
     * Mettre à jour un pointage existant.
     */
    public function update(Request $request, Project $project, Attendance $attendance)
    {
        abort_unless($attendance->project_id == $project->id, 404);

        $data = $this->validate($request, [
            'expected_arrival'   => 'nullable|date_format:H:i',
            'expected_departure' => 'nullable|date_format:H:i',
            'arrival_at'         => 'nullable|date',
            'departure_at'       => 'nullable|date',
            'break_minutes'      => 'nullable|integer|min:0',
            'status'             => 'nullable|string',
            'note'               => 'nullable|string',
        ]);

        $attendance->update($data);

        return $attendance->refresh();
    }

    /**
     * Supprimer un pointage.
     */
    public function destroy(Project $project, Attendance $attendance)
    {
        abort_unless($attendance->project_id == $project->id, 404);

        $attendance->delete();

        return ['message' => trans('common.success.deleted_resource')];
    }
}
