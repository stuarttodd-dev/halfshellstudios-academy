<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = ['organisation_id', 'title', 'user_id'];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
