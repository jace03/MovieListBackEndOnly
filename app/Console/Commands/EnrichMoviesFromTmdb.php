<?php

namespace App\Console\Commands;

use App\Models\Movie;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('app:enrich-movies-from-tmdb {--force : Overwrite existing year/poster_url values too}')]
#[Description('Fill in missing year/poster_url for movies by matching titles against the TMDB search API')]
class EnrichMoviesFromTmdb extends Command
{
    private const SEARCH_URL = 'https://api.themoviedb.org/3/search/movie';

    private const IMAGE_BASE = 'https://image.tmdb.org/t/p/w500';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $apiKey = config('services.tmdb.key');

        if (! $apiKey) {
            $this->error('Missing TMDB_API_KEY in .env.');

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $matched = 0;
        $skipped = 0;

        foreach (Movie::all() as $movie) {
            if (! $force && $movie->year !== null && $movie->poster_url !== null) {
                $skipped++;

                continue;
            }

            $result = $this->searchTmdb($apiKey, $movie->title, $movie->decade);

            if (! $result) {
                $this->warn("No TMDB match for \"{$movie->title}\" (id {$movie->id})");

                continue;
            }

            $year = $result['release_date'] ? (int) substr($result['release_date'], 0, 4) : null;
            $posterUrl = $result['poster_path'] ? self::IMAGE_BASE.$result['poster_path'] : null;

            $movie->update([
                'year' => $force ? $year : ($movie->year ?? $year),
                'poster_url' => $force ? $posterUrl : ($movie->poster_url ?? $posterUrl),
            ]);

            $matched++;
            $this->line("Matched \"{$movie->title}\" -> {$year}");
        }

        $this->info("Done. Matched {$matched}, skipped {$skipped} already-filled movies.");

        return self::SUCCESS;
    }

    /**
     * @return array{release_date: ?string, poster_path: ?string}|null
     */
    private function searchTmdb(string $apiKey, string $title, ?string $decade): ?array
    {
        $response = Http::get(self::SEARCH_URL, [
            'api_key' => $apiKey,
            'query' => $title,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $results = $response->json('results') ?? [];

        if ($results === []) {
            return null;
        }

        $best = $this->bestMatch($results, $decade) ?? $results[0];

        return [
            'release_date' => $best['release_date'] ?? null,
            'poster_path' => $best['poster_path'] ?? null,
        ];
    }

    /**
     * Prefer the result whose release year falls within the movie's known
     * decade (e.g. "1990s") over TMDB's default relevance/popularity order,
     * which tends to surface newer remakes/sequels for common titles.
     *
     * @param  array<int, array{release_date?: ?string}>  $results
     * @return array{release_date?: ?string, poster_path?: ?string}|null
     */
    private function bestMatch(array $results, ?string $decade): ?array
    {
        if (! $decade || ! preg_match('/^(\d{4})s$/', $decade, $matches)) {
            return null;
        }

        $start = (int) $matches[1];
        $end = $start + 9;

        foreach ($results as $result) {
            $year = $result['release_date'] ? (int) substr($result['release_date'], 0, 4) : null;

            if ($year !== null && $year >= $start && $year <= $end) {
                return $result;
            }
        }

        return null;
    }
}
