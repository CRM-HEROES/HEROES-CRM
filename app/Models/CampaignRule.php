<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRule extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'name',
        'type',
        'rule',
        'value',
        'style',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rule' => 'json',
        'style' => 'json',
    ];


    // Relationships

    /**
     * Campaigns
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_rule', 'rule_id', 'campaign_id');
    }

    /**
     * Operators
     */
    public function operators()
    {
        return $this->belongsToMany(CampaignOperator::class, 'campaign_rule_operator', 'rule_id', 'operator_id');
    }
}
