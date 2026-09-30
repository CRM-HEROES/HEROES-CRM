<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'folder_id',
        'from_user',
        'name',
        'pages_count',
        'path',
        'size',
        'source',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'path',
    ];


    /**
     *
     */
    protected $appends = [
        'url',
        'thumbnail',
    ];


    // Attributes

    /**
     * Get url
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return $this->prospect && $this->folder && $this->folder->project ? route('api.project.prospect.folder.file.show', [
            'project' => $this->folder->project->slug,
            'prospect' => $this->prospect->id,
            'folder' => $this->folder->id,
            'file' => $this->id
        ]) : null;
    }

    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnailAttribute()
    {
        return $this->prospect_id && $this->folder && $this->folder->project ? route('api.project.prospect.folder.file.thumbnail', [
            'project' => $this->folder->project->slug,
            'prospect' => $this->prospect_id,
            'folder' => $this->folder->id,
            'file' => $this->id
        ]) : null;
    }

    // Relationships

    /**
     * Prospect
     */
    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Folder
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Prospect
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    /**
     * Shared with users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_file');
    }
}
