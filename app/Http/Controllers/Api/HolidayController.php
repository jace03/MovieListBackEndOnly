<?php

namespace App\Http\Controllers\Api;

use App\Enums\WatchWindow;
use App\Http\Controllers\Controller;
use App\Http\Resources\HolidayResource;
use App\Http\Resources\MovieResource;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidayController extends Controller
{
    public function index()
    {
        return HolidayResource::collection(Holiday::orderBy('sort_order')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:holidays,name',
            'emoji' => 'nullable|string|max:16',
        ]);

        $holiday = Holiday::create([
            'name' => $validated['name'],
            'emoji' => $validated['emoji'] ?? '🎬',
            'sort_order' => (Holiday::max('sort_order') ?? 0) + 1,
        ]);

        return new HolidayResource($holiday);
    }

    /**
     * Update how many movies auto-calculate puts in each calendar slot.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'day_of_count' => 'sometimes|required|integer|min:0|max:100',
            'week_of_count' => 'sometimes|required|integer|min:0|max:100',
            'two_weeks_away_count' => 'sometimes|required|integer|min:0|max:100',
            'three_weeks_away_count' => 'sometimes|required|integer|min:0|max:100',
        ]);

        $holiday->update($validated);

        return new HolidayResource($holiday);
    }

    /**
     * Put every movie of the holiday back in month away. Only the slot is reset; no movie is deleted
     * and watched, rank and everything else stay as they are.
     */
    public function clearCalendar(Holiday $holiday)
    {
        $holiday->movies()->update(['watch_window' => WatchWindow::MonthAway]);

        return MovieResource::collection(
            $holiday->movies()->with(['holiday', 'actors'])->orderBy('rank')->orderBy('created_at')->get()
        );
    }

    /**
     * Fill the calendar slots from the holiday's ranking: the top movies go to day of,
     * the next to week of, and so on; everything left over lands in month away.
     */
    public function autoCalculateCalendar(Holiday $holiday)
    {
        $remaining = $holiday->movies()->orderBy('rank')->orderBy('created_at')->orderBy('id')->get()->values();

        DB::transaction(function () use ($holiday, $remaining) {
            foreach ($holiday->calendarSlotCounts() as $window => $count) {
                $remaining->splice(0, $count)->each->update(['watch_window' => $window]);
            }

            $remaining->each->update(['watch_window' => WatchWindow::MonthAway]);
        });

        return MovieResource::collection(
            $holiday->movies()->with(['holiday', 'actors'])->orderBy('rank')->orderBy('created_at')->get()
        );
    }
}
