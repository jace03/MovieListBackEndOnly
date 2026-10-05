<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    /** @use HasFactory<\Database\Factories\MovieFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'year',
        'added_by',
        'rating',
        'genre',
        'decade',
        'rank',
        'watched',
        'notes',
        'poster_url',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'rating' => 'integer',
            'rank' => 'integer',
            'watched' => 'boolean',
        ];
    }
}
