<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireOption extends Model
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
        'redirection_id',
        'order',
    ];


    // Relationships

    /**
     * Question
     */
    public function question()
    {
        return $this->belongsTo(QuestionnaireQuestion::class, 'question_id');
    }

    /**
     * Redirection question
     */
    public function redirection()
    {
        return $this->belongsTo(QuestionnaireQuestion::class, 'redirection_id');
    }
}
