<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MovieResource::collection(
            Movie::orderByRaw('`rank` IS NULL, `rank` ASC')->orderBy('created_at')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $movie = Movie::create($validated);

        return new MovieResource($movie);
    }

    /**
     * Display the specified resource.
     */
    public function show(Movie $movie)
    {
        return new MovieResource($movie);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movie $movie)
    {
        $validated = $this->validated($request, $movie);

        $movie->update($validated);

        return new MovieResource($movie);
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

        return new MovieResource($movie);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Movie $movie = null): array
    {
        $sometimes = $movie ? 'sometimes|' : '';

        return $request->validate([
            'title' => $sometimes.'required|string|max:255',
            'year' => 'nullable|integer',
            'added_by' => $sometimes.'required|in:His,Hers,Both',
            'rating' => 'nullable|integer|min:0|max:10',
            'genre' => 'nullable|string|max:255',
            'decade' => 'nullable|string|max:255',
            'holiday' => $sometimes.'required|in:Halloween,Christmas',
            'rank' => 'nullable|integer',
            'watched' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'poster_url' => 'nullable|string|max:2048',
        ]);
    }
}
