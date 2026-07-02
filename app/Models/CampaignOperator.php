<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignOperator extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'and',
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
     * Campaigns
     */
    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_operator', 'operator_id', 'campaign_id');
    }

    /**
     * Operators
     */
    public function operators()
    {
        return $this->belongsToMany(CampaignOperator::class, 'campaign_operator_operator', 'parent_id', 'child_id');
    }

    /**
     * Operators
     */
    public function parentOperators()
    {
        return $this->belongsToMany(CampaignOperator::class, 'campaign_operator_operator', 'child_id', 'parent_id');
    }

    /**
     * Rules
     */
    public function rules()
    {
        return $this->belongsToMany(CampaignRule::class, 'campaign_rule_operator', 'operator_id', 'rule_id');
    }
}
