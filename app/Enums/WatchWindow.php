<?php

namespace App\Enums;

/**
 * How far ahead of the holiday a movie is planned to be watched.
 * Anything not given a closer slot sits in MonthAway.
 */
enum WatchWindow: string
{
    case DayOf = 'day_of';
    case WeekOf = 'week_of';
    case TwoWeeksAway = 'two_weeks_away';
    case ThreeWeeksAway = 'three_weeks_away';
    case MonthAway = 'month_away';
}
