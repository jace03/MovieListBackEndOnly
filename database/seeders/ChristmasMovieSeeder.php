<?php

namespace Database\Seeders;

use App\Models\Movie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChristmasMovieSeeder extends Seeder
{
    /**
     * Import the Christmas-tagged movies from the legacy WAMP `movieslist`
     * Laravel app (MySQL) into this app's `movies` table.
     */
    public function run(): void
    {
        $legacyMovies = DB::connection('legacy')
            ->table('movies')
            ->where('holiday', 'Christmas')
            ->get();

        foreach ($legacyMovies as $legacy) {
            Movie::create([
                'title' => $legacy->title,
                'year' => null,
                'added_by' => 'Both',
                'rating' => 0,
                'genre' => $legacy->genre,
                'decade' => $legacy->decade === '1990' ? '1990s' : $legacy->decade,
                'holiday' => 'Christmas',
                'rank' => $legacy->rating,
                'watched' => false,
                'notes' => $legacy->description ?? '',
                'poster_url' => null,
            ]);
        }
    }
}
