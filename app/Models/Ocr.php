<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ocr extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'path',
        'size',
        'prediction',
        'api',
        'type',
        'prospect_id',
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
     * Project
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

}
