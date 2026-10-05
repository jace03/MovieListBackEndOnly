<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'emoji',
        'sort_order',
    ];

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }
}
