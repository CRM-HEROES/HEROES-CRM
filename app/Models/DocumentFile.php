<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFile extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'name',
        'path',
        'size',
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
    ];


    // Attributes

    /**
     * Get font url
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return route('api.project.document.file.show', [
            'project' => $this->document->project->slug, 
            'document' => $this->document->id, 
            'file' => $this->id
        ]);
    }


    // Relationships

    /**
     * Document
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Pages
     */
    public function pages()
    {
        return $this->hasMany(Documentpage::class, 'file_id');
    }
}
