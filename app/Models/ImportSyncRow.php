<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One lead received from Google Sheets. See the import_sync_rows migration.
 */
class ImportSyncRow extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_CREATED = 'created';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_FAILED = 'failed';

    /** Attempts after which a row is given up on (status "failed"). */
    public const MAX_ATTEMPTS = 5;

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'claimed_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
