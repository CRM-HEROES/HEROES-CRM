<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDocument extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'product_id',
        'name',
        'path',
        'size',
    ];
    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'path',
    ];


    /**
     * 
     */
    protected $appends = [
        'url',
    ];


    // Attributes

    /**
     * Get font url
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return route('api.project.product.document.show', [
            'project' => $this->product->project->slug, 
            'product' => $this->product->id,
            'document' => $this->id
        ]);
    }


    // Relationships

    /**
     * Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
