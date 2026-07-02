<?php

namespace App\Models;

use App\Models\Scopes\QuestionnaireScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;
    
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new QuestionnaireScope());
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'name',
        'order',
    ];


    // Attributes

    /**
     * Sections
     */
    public function sections()
    {
        return $this->hasMany(QuestionnaireSection::class);
    }

    /**
     * Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_questionnaire');
    }
    
    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_questionnaire');
    }
}
