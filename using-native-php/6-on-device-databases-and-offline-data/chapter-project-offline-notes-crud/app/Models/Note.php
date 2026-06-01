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
    ];
}
