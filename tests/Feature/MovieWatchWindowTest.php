<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Movie;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MovieWatchWindowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The actors tables exist in the real database but not in the migrations, and the API loads them.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('movie_actor', function (Blueprint $table) {
            $table->foreignId('movie_id');
            $table->foreignId('actor_id');
        });
    }

    private function createMovie(array $overrides = []): Movie
    {
        return Movie::create(array_merge([
            'title' => 'Hocus Pocus',
            'added_by' => 'Both',
            'holiday_id' => Holiday::where('name', 'Halloween')->value('id'),
        ], $overrides));
    }

    public function test_new_movies_default_to_month_away(): void
    {
        $this->postJson('/api/movies', ['title' => 'Casper', 'added_by' => 'Both', 'holiday' => 'Halloween'])
            ->assertCreated()
            ->assertJsonPath('data.watch_window', 'month_away');
    }

    public function test_watch_window_can_be_set_on_update(): void
    {
        $movie = $this->createMovie();

        $this->patchJson("/api/movies/{$movie->id}", ['watch_window' => 'day_of'])
            ->assertOk()
            ->assertJsonPath('data.watch_window', 'day_of');

        $this->assertSame('day_of', $movie->fresh()->watch_window->value);
    }

    public function test_an_unknown_watch_window_is_rejected(): void
    {
        $movie = $this->createMovie();

        $this->patchJson("/api/movies/{$movie->id}", ['watch_window' => 'next_year'])
            ->assertUnprocessable();
    }

    public function test_a_plain_update_keeps_the_existing_watch_window(): void
    {
        $movie = $this->createMovie(['watch_window' => 'week_of']);

        $this->putJson("/api/movies/{$movie->id}", ['title' => 'Hocus Pocus 2'])->assertOk();

        $this->assertSame('week_of', $movie->fresh()->watch_window->value);
    }
}
