<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclePosition extends Model
{
    use HasFactory;
    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'latitude',
        'longitude'
    ];

    // Relationships

    /**
     * Project
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
