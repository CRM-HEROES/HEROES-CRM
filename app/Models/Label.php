<?php

namespace App\Models;

use App\Models\Scopes\LabelScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Label extends Model
{
    use HasFactory, SoftDeletes;
    
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new LabelScope());
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
        'description',
        'name',
        'order',
        'archived_at'
    ];

    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'archived_at' => 'datetime',
    ];

    
    protected $hidden = ['pivot'];


    // Relationships

    /**
     * Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Orders
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_label');
    }

    /**
     * Prospects
     */
    public function prospects()
    {
        return $this->belongsToMany(Prospect::class, 'prospect_label');
    }

    /**
     * Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_label');
    }
    
    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_label');
    }
}
