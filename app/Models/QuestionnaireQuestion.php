<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireQuestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'is_required',
        'name',
        'order',
        'type',
        'order',
    ];


    // Attributes

    /**
     * Options
     */
    public function options()
    {
        return $this->hasMany(QuestionnaireOption::class, 'question_id');
    }

    /**
     * Section
     */
    public function section()
    {
        return $this->belongsTo(QuestionnaireSection::class, 'section_id');
    }
}
