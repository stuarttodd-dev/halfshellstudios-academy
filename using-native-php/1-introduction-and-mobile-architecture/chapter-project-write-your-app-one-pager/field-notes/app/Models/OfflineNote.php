<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineNote extends Model
{
    public const SYNC_PENDING = 'pending';

    public const SYNC_SYNCED = 'synced';

    public const SYNC_FAILED = 'failed';

    /** @var list<string> */
    protected $fillable = [
        'body',
        'sync_state',
    ];

    public function isPending(): bool
    {
        return $this->sync_state === self::SYNC_PENDING;
    }
}
