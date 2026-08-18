<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per ARCHER nightly enrichment attempt for a prospect (see
 * App\Services\Archer and App\Jobs\ArcherEnrichProspect). Kept as full
 * history, mirroring ProspectCallQualification, rather than a single
 * "current state" row — so past runs stay auditable.
 */
class ProspectEnrichment extends Model
{
    protected $fillable = [
        'prospect_id',
        'status',
        'email_verified',
        'email_verified_at',
        'phone_verified',
        'phone_verified_at',
        'linkedin_url',
        'dropcontact_data',
        'proxycurl_data',
        'score',
        'percentile',
        'is_top_20',
        'error',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'phone_verified' => 'boolean',
        'phone_verified_at' => 'datetime',
        'dropcontact_data' => 'json',
        'proxycurl_data' => 'json',
        'percentile' => 'float',
        'is_top_20' => 'boolean',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }
}
