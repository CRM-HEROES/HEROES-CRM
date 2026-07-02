<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'path',
        'creator_id',
        'data',
        'ended_at',
        'from_user',
        'number',
        'from_number',
        'size',
        'source',
        'status',
        'started_at',
    ];
    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'size',
        'path',
    ];


    /**
     * 
     */
    protected $appends = [
        'audio',
    ];


    // Attributes

    /**
     * Get font url
     *
     * @return string
     */
    public function getAudioAttribute()
    {
        return $this->path ? route('api.project.prospect.interaction.audio', [
            'project' => $this->prospect->project->slug, 
            'prospect' => $this->prospect->id, 
            'interaction' => $this->id
        ]) : null;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'json',
    ];


    // Relationships

    /**
     * Groups
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Prospect
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
