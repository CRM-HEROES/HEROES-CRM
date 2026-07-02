<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_id',
        'order_id',
        'creator_id',
        'path',
        'size',
    ];


    // Relationships

    /**
     * Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Document
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
