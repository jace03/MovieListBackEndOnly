<?php

namespace App\Models;

use App\Enums\WatchWindow;
use Database\Factories\MovieFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    /** @use HasFactory<MovieFactory> */
    use HasFactory;

    /**
     * Mirrors the column default so a freshly created model already carries it.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'watch_window' => 'month_away',
    ];

    protected $fillable = [
        'title',
        'year',
        'added_by',
        'rating',
        'genre',
        'decade',
        'holiday_id',
        'rank',
        'watch_window',
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
            'watch_window' => WatchWindow::class,
            'watched' => 'boolean',
        ];
    }

    public function holiday(): BelongsTo
    {
        return $this->belongsTo(Holiday::class);
    }

    public function actors(): BelongsToMany
    {
        return $this->belongsToMany(Actor::class, 'movie_actor');
    }
}
