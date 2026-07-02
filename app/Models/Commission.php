<?php

namespace App\Models;

use App\Filters\Filters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'action_id',
        'creator_id',
        'including_tax',
        'name',
        'product_id',
        'type',
        'user_id',
        'value',
    ];


    /**
     * 
     */
    protected $appends = [
        'absolute_value',
    ];


    // Attributes

    /**
     * Get total excluding tax
     *
     * @return string
     */
    public function getAbsoluteValueAttribute()
    {
        // Fix commission
        if ($this->type == 'fix') {
            return $this->value;
        }

        // Percentage commission from product including tax price
        if ($this->including_tax) {
            return $this->product->price_including_tax * $this->value;
        }

        // Percentage commission from product excluding tax price
        return $this->product->price_excluding_tax * $this->value;
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
     * Action
     */
    public function action()
    {
        return $this->belongsTo(OrderAction::class, 'action_id');
    }

    /**
     * Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
