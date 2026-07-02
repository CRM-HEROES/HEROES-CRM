<?php

namespace App\Models;

use App\Filters\Filters;
use App\Models\Scopes\DocumentScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new DocumentScope());
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'for',
        'name',
        'order',
        'folder_id',
    ];


    /**
     * 
     */
    protected $appends = [
        'thumbnail',
    ];


    // Attributes

    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnailAttribute()
    {
        return /*$this->files()->first() && */$this->project ? route('api.project.document.thumbnail', [
            'project' => $this->project->slug, 
            'document' => $this->id
        ]) : null;
    }


    // Builder

    /**
     * Apply all relevant filters
     *
     * @param Builder $query
     * @param Filters $filters
     * @return Builder
     */
    public function scopeFilter(Builder $builder, Filters $filters)
    {
        return $filters->apply($builder);
    }


    // Relationships

    /**
     * Fields
     */
    public function fields()
    {
        return $this->hasMany(DocumentField::class);
    }

    /**
     * Files
     */
    public function files()
    {
        return $this->hasMany(DocumentFile::class);
    }

    /**
     * Fonts
     */
    public function fonts()
    {
        return $this->hasMany(DocumentFont::class);
    }

    /**
     * Pages
     */
    public function pages()
    {
        return $this->hasMany(Documentpage::class);
    }

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_document');
    }
    
    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_document');
    }
}
