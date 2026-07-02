<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatChart extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'chart_type',
        'labels',
        'options',
        'order',
        'creator_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'labels' => 'json',
        'options' => 'json',
    ];


    // Relationships

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
