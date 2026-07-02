<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
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
        'order',
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
        'product',
    ];


    /**
     * 
     */
    protected $appends = [
        'url',
        'thumbnail',
    ];


    // Attributes

    /**
     * Get url
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return route('api.project.product.image.show', [
            'project' => $this->product->project->slug, 
            'product' => $this->product->id,
            'image' => $this->id
        ]);
    }

    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnailAttribute()
    {
        return route('api.project.product.image.thumbnail', [
            'project' => $this->product->project->slug, 
            'product' => $this->product->id,
            'image' => $this->id
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
