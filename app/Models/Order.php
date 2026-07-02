<?php

namespace App\Models;

use App\Filters\Filters;
use App\Models\Scopes\OrderScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderProductPivot extends Pivot
{
    use HasFactory;
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new OrderScope());
    }
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quantity',
        'currency',
        'including_tax',
        'price',
        'tax',
        'order',
        'meta',
        'creator_id',
        'created_at',
        'updated_at',
    ];


    /**
     * 
     */
    protected $appends = [
        'price_value',
        'tax_percent',
        'price_excluding_tax',
        'price_including_tax',
        'tax_value',
        'total_price_excluding_tax',
        'total_price_including_tax',
        'total_tax_value',
    ];

    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meta' => 'json',
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

    /**
     * Get price excluding tax
     *
     * @return string
     */
    public function getTotalPriceExcludingTaxAttribute()
    {
        return $this->price_excluding_tax * $this->quantity;
    }

    /**
     * Get tax
     *
     * @return string
     */
    public function getTotalTaxValueAttribute()
    {
        return $this->tax_value * $this->quantity;
    }

    /**
     * Get price including tax
     *
     * @return string
     */
    public function getTotalPriceIncludingTaxAttribute()
    {
        return $this->price_including_tax * $this->quantity;
    }
}


class OrderActionPivot extends Pivot
{
    protected $hidden = [
        'us'
    ];

    protected $appends = [
        'user'
    ];

    public function getUserAttribute() {
        return $this->us;
    }

    /**
     * User associated with the Action.
     */
    public function us()
    {
        return $this->belongsTo(User::class, 'user_id')->select('id', 'name', 'email');
    }
}

class Order extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        // 'name',
        'description',
        'number',
        'project_id',
        'status_id',
        'meta',
    ];


    /**
     * 
     */
    protected $appends = [
        'name',
        'currency',
        'total_excluding_tax',
        'total_including_tax',
        'tax_value',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meta' => 'json',
        'created_at' => 'datetime',
    ];


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


    // Attributes

    /**
     * Get name
     *
     * @return string
     */
    public function getNameAttribute()
    {
        if ($this->products->count() == 0) {
            return "Choisir un produit ...";
        }

        return $this->products->map(function($product) {
            return $product->name;
        })->join(", ");
    }

    /**
     * Get total excluding tax
     *
     * @return string
     */
    public function getTotalExcludingTaxAttribute()
    {
        return $this->products()->withPivot(['price', 'tax', 'including_tax', 'quantity'])->get()->reduce(function ($carry, $product) {
            return $carry + $product->pivot->price_excluding_tax * $product->pivot->quantity;
        }, 0);
    }

    /**
     * Get tax
     *
     * @return string
     */
    public function getTaxValueAttribute()
    {
        return $this->products()->withPivot(['price', 'tax', 'including_tax', 'quantity'])->get()->reduce(function ($carry, $product) {
            return $carry + $product->pivot->tax_value * $product->pivot->quantity;
        }, 0);
    }

    /**
     * Get total including tax
     *
     * @return string
     */
    public function getTotalIncludingTaxAttribute()
    {
        return $this->products()->withPivot(['price', 'tax', 'including_tax', 'quantity'])->get()->reduce(function ($carry, $product) {
            return $carry + $product->pivot->price_including_tax * $product->pivot->quantity;
        }, 0);
    }

    /**
     * Get Order currency
     */
    public function getCurrencyAttribute()
    {
        return $this->products->count() == 0 ? "" : $this->products->first()->pivot->currency;
    }

    /**
     * Get total with tax
     *
     * @return string
     */
    public function getDateAttribute()
    {
        return $this->created_at->format("d/m/Y");
    }


    // Relationships

    /**
     * Actions
     */
    public function actions()
    {
        return $this->belongsToMany(OrderAction::class, 'order_action', 'order_id', 'action_id')->withPivot(['user_id', 'paid'])->using(OrderActionPivot::class);
    }

    /**
     * Campaigns
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_order');
    }

    /**
     * Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Invoices
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Products
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')->using(OrderProductPivot::class)->withPivot(['price', 'tax', 'including_tax', 'quantity', 'currency', 'meta']);
    }

    /**
     * Prospect
     */
    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }

    /**
     * Status
     */
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    /**
     * Steps
     */
    public function steps()
    {
        return $this->belongsToMany(OrderStep::class, 'order_step', 'order_id', 'step_id');
    }
}
