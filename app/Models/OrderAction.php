<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderAction extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'order',
        'description',
        'color',
        'bgcolor',
        'default_role_id',
    ];

    // Relationships

    /**
     * Default role
     */
    public function defaultRole()
    {
        return $this->belongsTo(Role::class, 'default_role_id');
    }

}
