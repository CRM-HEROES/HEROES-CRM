<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PipedriveAccount extends Model
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
        'password',
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
     * Prospects
     */
    public function prospects()
    {
        return $this->belongsToMany(Prospect::class, "pipedrive_person", "pipedrive_account_id", "prospect_id");
    }
}
