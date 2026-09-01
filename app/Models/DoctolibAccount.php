<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctolibAccount extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
        'name',
        'token',
        'deleted_at',
    ];


    // Relationships

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Local calendars this account imports appointments into
     */
    public function calendars()
    {
        return $this->belongsToMany(Calendar::class, 'doctolib_calendar', 'doctolib_account_id', 'calendar_id')
            ->withPivot('doctolib_calendar_id');
    }

    /**
     * Events imported from this account
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'doctolib_event', 'doctolib_account_id', 'event_id')
            ->withPivot('doctolib_event_id');
    }
}
