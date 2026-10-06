<?php

namespace App\Http\Controllers\Api;

use App\Enums\WatchWindow;
use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Models\Holiday;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MovieResource::collection(
            Movie::with(['holiday', 'actors'])->orderBy('rank')->orderBy('created_at')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $movie = Movie::create($validated);

        return new MovieResource($movie->load(['holiday', 'actors']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Movie $movie)
    {
        return new MovieResource($movie->load(['holiday', 'actors']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movie $movie)
    {
        $validated = $this->validated($request, $movie);

        $movie->update($validated);

        return new MovieResource($movie->load(['holiday', 'actors']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movie $movie)
    {
        $movie->delete();

        return response()->noContent();
    }

    /**
     * Flip the watched flag on the specified movie.
     */
    public function toggleWatched(Movie $movie)
    {
        $movie->update(['watched' => ! $movie->watched]);

        return new MovieResource($movie->load(['holiday', 'actors']));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Movie $movie = null): array
    {
        $sometimes = $movie ? 'sometimes|' : '';

        $validated = $request->validate([
            'title' => $sometimes.'required|string|max:255',
            'year' => 'nullable|integer',
            'added_by' => $sometimes.'required|in:His,Hers,Both',
            'rating' => 'nullable|integer|min:0|max:10',
            'genre' => 'nullable|string|max:255',
            'decade' => 'nullable|string|max:255',
            'holiday' => $sometimes.'required|string|exists:holidays,name',
            'rank' => 'nullable|integer|min:1|max:100',
            'watch_window' => ['sometimes', Rule::enum(WatchWindow::class)],
            'watched' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'poster_url' => 'nullable|string|max:2048',
        ]);

        // The API speaks holiday names; the database stores the holiday_id.
        if (isset($validated['holiday'])) {
            $validated['holiday_id'] = Holiday::where('name', $validated['holiday'])->value('id');
            unset($validated['holiday']);
        }

        // Rank 1 is the top of the list; 100 means unranked.
        if (array_key_exists('rank', $validated) && $validated['rank'] === null) {
            $validated['rank'] = 100;
        }

        return $validated;
    }
}
