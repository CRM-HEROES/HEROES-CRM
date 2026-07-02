<?php

namespace App\Models;

use App\Filters\Filters;
use App\Models\Scopes\FolderScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory, SoftDeletes;


    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new FolderScope());
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bgcolor',
        'color',
        'creator_id',
        'name',
        'private',
        'slug',
        'for',
        'order',
    ];


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
        return $this->belongsToMany(Role::class, 'role_folder');
    }

    /**
     * Files
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_folder');
    }
}
