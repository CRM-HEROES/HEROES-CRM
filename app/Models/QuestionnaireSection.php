<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireSection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'name',
        'order',
        'order',
    ];


    // Attributes

    /**
     * Questions
     */
    public function questions()
    {
        return $this->hasMany(QuestionnaireQuestion::class, 'section_id');
    }

    /**
     * Questionnaire
     */
    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }
}
