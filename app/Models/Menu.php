<?php

namespace App\Models;

use App\Models\Scopes\MenuScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new MenuScope());
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'name',
        'color',
        'bgcolor',
        'filters',
        'order',
        'for',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'filters' => 'json',
    ];


    // Attributes
    
    /**
     * Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_menu');
    }
    
    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_menu');
    }
}
