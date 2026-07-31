<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KavkomCall extends Model
{
    protected $fillable = ['call_uuid', 'prospect_id', 'interaction_id', 'user_id', 'domain_uuid', 'destination', 'status', 'recording_url', 'webhook_payload', 'error', 'completed_at', 'processed_at'];

    protected $casts = ['webhook_payload' => 'array', 'completed_at' => 'datetime', 'processed_at' => 'datetime'];

    public function prospect() { return $this->belongsTo(Prospect::class); }
    public function interaction() { return $this->belongsTo(Interaction::class); }
}
