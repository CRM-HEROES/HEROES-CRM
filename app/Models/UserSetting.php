<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'project_id',
        'user_id',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'json',
    ];
    
    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
