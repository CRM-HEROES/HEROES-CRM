<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
        'date',
        'expected_arrival',
        'expected_departure',
        'arrival_at',
        'departure_at',
        'break_minutes',
        'status',
        'note',
        'creator_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date'         => 'date',
        'arrival_at'   => 'datetime',
        'departure_at' => 'datetime',
    ];

    /**
     * Attributs calculés ajoutés à la sérialisation.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'retard_minutes',
        'worked_minutes',
    ];


    // Relationships

    /**
     * Projet
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Employé concerné
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Créateur du pointage (l'admin)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }


    // Attributes

    /**
     * Minutes de retard = arrivée réelle - arrivée prévue.
     * 0 si à l'heure ou si l'info manque.
     */
    public function getRetardMinutesAttribute()
    {
        if (!$this->arrival_at || !$this->expected_arrival) {
            return 0;
        }

        $expected = Carbon::parse(
            $this->date->format('Y-m-d') . ' ' . $this->expected_arrival
        );

        $diff = $expected->diffInMinutes($this->arrival_at, false);

        return $diff > 0 ? $diff : 0;
    }

    /**
     * Minutes travaillées = départ - arrivée - pause.
     */
    public function getWorkedMinutesAttribute()
    {
        if (!$this->arrival_at || !$this->departure_at) {
            return 0;
        }

        $minutes = $this->arrival_at->diffInMinutes($this->departure_at)
            - ($this->break_minutes ?? 0);

        return $minutes > 0 ? $minutes : 0;
    }
}
