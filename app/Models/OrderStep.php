<?php

namespace App\Models;

use App\Models\Scopes\OrderStepScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStep extends Model
{
    use HasFactory;
    
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new OrderStepScope());
    }
    
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
    ];

    /**
     * Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_order_step', 'step_id', 'role_id');
    }
    
    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_order_step', 'step_id', 'user_id');
    }
}
