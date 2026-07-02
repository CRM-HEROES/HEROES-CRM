<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    use HasFactory;

    /**
     * Table
     */
    protected $table = "sms";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'created_at',
        'creator_id',
        'from_user',
        'message',
        'error',
        'project_id',
        'source',
    ];
    
    protected $casts = [
        'sent_at' => 'datetime'
    ];


    // Attributes

    /**
     * Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Prospect
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
