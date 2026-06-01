<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'body',
        'sync_pending',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sync_pending' => 'boolean',
        ];
    }
}
