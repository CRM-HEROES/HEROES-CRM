<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProspectQuestionnaireResponse extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'option_id',
        'prospect_id',
        'question_id',
        'response',
        'size',
    ];


    /**
     * 
     */
    protected $appends = [
        'url',
    ];


    // Attributes

    /**
     * Get url
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        if ($this->question->type != 'image' && $this->question->type != 'file') {
            return "";
        }

        return route('api.project.prospect.question.show', [
            'project' => $this->prospect->project->slug, 
            'prospect' => $this->prospect->id, 
            'question' => $this->question->id
        ]);
    }


    // Relationships

    /**
     * Option
     */
    public function option()
    {
        return $this->belongsTo(QuestionnaireOption::class, 'option_id');
    }

    /**
     * Prospect
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }

    /**
     * Question
     */
    public function question()
    {
        return $this->belongsTo(QuestionnaireQuestion::class, 'question_id');
    }
}
