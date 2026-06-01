<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevicePushToken extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'token',
        'platform',
    ];
}
