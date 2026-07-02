<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentPage extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'file_id',
        'file_page',
        'page',
        'width',
        'height',
        'orientation',
    ];


    // Relationships

    /**
     * Document
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Fields
     */
    public function fields()
    {
        return $this->hasMany(DocumentField::class, 'page_id');
    }
    
    /**
     * File
     */
    public function file()
    {
        return $this->belongsTo(DocumentFile::class, 'file_id');
    }

    /**
     * Rules
     */
    public function rules()
    {
        return $this->belongsToMany(QuestionnaireOption::class, 'document_page_rules', 'page_id', 'option_id');
    }
}
