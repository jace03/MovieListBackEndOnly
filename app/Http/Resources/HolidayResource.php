<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'emoji' => $this->emoji,
            'sort_order' => $this->sort_order,
            'day_of_count' => $this->day_of_count,
            'week_of_count' => $this->week_of_count,
            'two_weeks_away_count' => $this->two_weeks_away_count,
            'three_weeks_away_count' => $this->three_weeks_away_count,
        ];
    }
}
