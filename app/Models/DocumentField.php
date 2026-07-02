<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentField extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content',
        'creator_id',
        'page_id',
        'type',
        'style',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'style' => 'json',
    ];


    // Relationships

    /**
     * Page
     */
    public function page()
    {
        return $this->belongsTo(DocumentPage::class, 'page_id');
    }


    // Attributes

    /**
     * Get field css
     *
     * @return string
     */
    public function getCssAttribute()
    {
        return implode('; ', 
            array_map(
                function($key, $value) {
                    return $key . ': ' . $value;
                }, 
                array_keys($this->style), 
                array_values($this->style)
            )
        );
    }
}
