<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectCallQualification extends Model
{
    protected $fillable = [
        'prospect_id', 'kavkom_call_id', 'interaction_id', 'score_before',
        'score_after', 'qualification', 'conversion_probability', 'analysis',
    ];

    protected $casts = ['analysis' => 'array'];

    public function prospect() { return $this->belongsTo(Prospect::class); }
    public function call() { return $this->belongsTo(KavkomCall::class, 'kavkom_call_id'); }
    public function interaction() { return $this->belongsTo(Interaction::class); }
}
