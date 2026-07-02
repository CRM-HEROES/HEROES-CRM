<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoogleAccount extends Model
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
        'google_id',
        'name',
        'for',
        'token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'token' => 'json',
    ];


    // Relationships

    /**
     * Calendars
     */
    public function calendars()
    {
        return $this->belongsToMany(Calendar::class, "google_calendar");
    }

    /**
     * Contacts
     */
    public function contacts()
    {
        return $this->belongsToMany(Prospect::class, "google_contact", "google_account_id", "prospect_id");
    }

    /**
     * Events
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, "google_event");
    }

    /**
     * Files
     */
    public function files()
    {
        return $this->belongsToMany(File::class, "google_file");
    }

    /**
     * Project folders
     */
    public function projectFolders()
    {
        return $this->belongsToMany(Project::class, "google_folder", "google_account_id", "project_id")->wherePivotNotNull("project_id")->wherePivotNull("prospect_id")->wherePivotNull("folder_id");
    }

    /**
     * Prospect folders
     */
    public function prospectFolders()
    {
        return $this->belongsToMany(Prospect::class, "google_folder", "google_account_id", "folder_id")->wherePivotNotNull("project_id")->wherePivotNotNull("prospect_id")->wherePivotNull("folder_id");
    }
    
    /**
     * Folder folders
     */
    public function folderFolders()
    {
        return $this->belongsToMany(Folder::class, "google_folder", "google_account_id", "prospect_id")->wherePivotNotNull("project_id")->wherePivotNotNull("prospect_id")->wherePivotNotNull("folder_id");
    }

}
