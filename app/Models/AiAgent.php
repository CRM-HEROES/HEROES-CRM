<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'creator_id',
        'name',
        'is_active',
        'script',
        'instructions',
        'config',
        'kavkom_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'kavkom_config' => 'encrypted:array',
    ];

    protected $hidden = [
        'kavkom_config',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getPublicKavkomConfigAttribute(): array
    {
        $config = $this->kavkom_config ?: [];

        foreach (['api_token', 'password', 'refresh_access_token', 'refresh_bearer'] as $secret) {
            if (array_key_exists($secret, $config)) {
                $config[$secret] = $config[$secret] ? '********' : null;
            }
        }

        return $config;
    }
}
