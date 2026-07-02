<?php

namespace App\Models;

use App\Filters\Filters;
use App\Models\Scopes\ProductScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ProductScope());
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'currency',
        'description',
        'name',
        'price',
        'tax',
        'including_tax',
        'order',
        'archived_at',
    ];

    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'archived_at' => 'datetime',
    ];


    /**
     * 
     */
    protected $appends = [
        'price_value',
        'price_excluding_tax',
        'price_including_tax',
        'tax_percent',
        'tax_value',
    ];


    // Attributes

    /**
     * Get tax percent
     *
     * @return string
     */
    public function getTaxPercentAttribute()
    {
        return $this->tax * 100;
    }

    /**
     * Get tax percent
     *
     * @return string
     */
    public function getPriceValueAttribute()
    {
        return $this->price . $this->currency . " " . ($this->including_tax ? trans('order.including_tax') : trans('order.excluding_tax'));
    }

    /**
     * Get price excluding tax
     *
     * @return string
     */
    public function getPriceExcludingTaxAttribute()
    {
        return $this->including_tax ? ($this->price / (1 + $this->tax)) : $this->price;
    }

    /**
     * Get tax
     *
     * @return string
     */
    public function getTaxValueAttribute()
    {
        return $this->price_excluding_tax * $this->tax;
    }

    /**
     * Get price including tax
     *
     * @return string
     */
    public function getPriceIncludingTaxAttribute()
    {
        return $this->including_tax ? $this->price : ($this->price * (1 + $this->tax));
    }


    // Builder

    /**
     * Apply all relevant filters
     *
     * @param Builder $query
     * @param Filters $filters
     * @return Builder
     */
    public function scopeFilter(Builder $builder, Filters $filters)
    {
        return $filters->apply($builder);
    }


    // Relationships

    /**
     * Documents
     */
    public function documents()
    {
        return $this->hasMany(ProductDocument::class);
    }

    /**
     * Images
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    /**
     * Roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_product');
    }
    
    /**
     * Users
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_product');
    }
}
