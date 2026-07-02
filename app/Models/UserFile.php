<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserFile extends Model
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
        return $this->user && $this->folder && $this->folder->project ? route('api.project.user.folder.file.show', [
            'project' => $this->folder->project->slug, 
            'user' => $this->user->id, 
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
        return $this->user && $this->folder && $this->folder->project ? route('api.project.user.folder.file.thumbnail', [
            'project' => $this->folder->project->slug, 
            'user' => $this->user->id, 
            'folder' => $this->folder->id, 
            'file' => $this->id
        ]) : null;
    }

    // Relationships

    /**
     * User
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
     * User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
