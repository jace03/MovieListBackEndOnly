<?php

namespace App\Models;

use App\Enums\WatchWindow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'emoji',
        'sort_order',
        'day_of_count',
        'week_of_count',
        'two_weeks_away_count',
        'three_weeks_away_count',
    ];

    /**
     * Mirrors the column defaults so a freshly created model already carries them.
     *
     * @var array<string, int>
     */
    protected $attributes = [
        'day_of_count' => 3,
        'week_of_count' => 7,
        'two_weeks_away_count' => 4,
        'three_weeks_away_count' => 4,
    ];

    /**
     * How many movies auto-calculate puts in each slot, in calendar order. The rest fall to month away.
     *
     * @return array<string, int>
     */
    public function calendarSlotCounts(): array
    {
        return [
            WatchWindow::DayOf->value => $this->day_of_count,
            WatchWindow::WeekOf->value => $this->week_of_count,
            WatchWindow::TwoWeeksAway->value => $this->two_weeks_away_count,
            WatchWindow::ThreeWeeksAway->value => $this->three_weeks_away_count,
        ];
    }

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
    }
}
