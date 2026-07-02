<?php

namespace App\Models;

use App\Models\Scopes\PipelineScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipeline extends Model
{
    use HasFactory, SoftDeletes;
    
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new PipelineScope());
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'description',
        'name',
        'color',
        'bgcolor',
        'order',
    ];


    // Relationships

    /**
     * Labels
     */
    public function labels()
    {
        return $this->belongsToMany(Label::class, 'pipeline_label')->withPivot('order');
    }

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
