<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Movie;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HolidayCalendarTest extends TestCase
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

    private function createMovies(Holiday $holiday, int $count): void
    {
        foreach (range(1, $count) as $position) {
            Movie::create([
                'title' => "Movie {$position}",
                'added_by' => 'Both',
                'holiday_id' => $holiday->id,
                'rank' => $position,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function windowsByRank(Holiday $holiday): array
    {
        return $holiday->movies()->orderBy('rank')->get()->map(fn (Movie $movie) => $movie->watch_window->value)->all();
    }

    public function test_holidays_start_with_the_default_slot_sizes(): void
    {
        $this->getJson('/api/holidays')
            ->assertOk()
            ->assertJsonPath('data.0.day_of_count', 3)
            ->assertJsonPath('data.0.week_of_count', 7)
            ->assertJsonPath('data.0.two_weeks_away_count', 4)
            ->assertJsonPath('data.0.three_weeks_away_count', 4);
    }

    public function test_auto_calculate_fills_slots_from_the_ranking(): void
    {
        $holiday = Holiday::where('name', 'Halloween')->firstOrFail();
        $this->createMovies($holiday, 25);

        $this->postJson("/api/holidays/{$holiday->id}/auto-calculate-calendar")->assertOk()->assertJsonCount(25, 'data');

        $expected = array_merge(
            array_fill(0, 3, 'day_of'),
            array_fill(0, 7, 'week_of'),
            array_fill(0, 4, 'two_weeks_away'),
            array_fill(0, 4, 'three_weeks_away'),
            array_fill(0, 7, 'month_away'),
        );
        $this->assertSame($expected, $this->windowsByRank($holiday));
    }

    public function test_auto_calculate_uses_the_holidays_own_sizes_and_resets_old_slots(): void
    {
        $holiday = Holiday::where('name', 'Halloween')->firstOrFail();
        $this->createMovies($holiday, 4);
        $holiday->movies()->update(['watch_window' => 'day_of']);

        $this->patchJson("/api/holidays/{$holiday->id}", ['day_of_count' => 1, 'week_of_count' => 1])->assertOk();
        $this->postJson("/api/holidays/{$holiday->id}/auto-calculate-calendar")->assertOk();

        $this->assertSame(['day_of', 'week_of', 'two_weeks_away', 'two_weeks_away'], $this->windowsByRank($holiday));
    }

    public function test_auto_calculate_leaves_other_holidays_alone(): void
    {
        $halloween = Holiday::where('name', 'Halloween')->firstOrFail();
        $christmas = Holiday::where('name', 'Christmas')->firstOrFail();
        $this->createMovies($christmas, 2);

        $this->postJson("/api/holidays/{$halloween->id}/auto-calculate-calendar")->assertOk();

        $this->assertSame(['month_away', 'month_away'], $this->windowsByRank($christmas));
    }

    public function test_clear_resets_only_the_slots_and_keeps_every_movie(): void
    {
        $halloween = Holiday::where('name', 'Halloween')->firstOrFail();
        $christmas = Holiday::where('name', 'Christmas')->firstOrFail();
        $this->createMovies($halloween, 5);
        $this->createMovies($christmas, 2);
        $this->postJson("/api/holidays/{$halloween->id}/auto-calculate-calendar")->assertOk();
        $this->postJson("/api/holidays/{$christmas->id}/auto-calculate-calendar")->assertOk();
        $halloween->movies()->where('rank', 1)->update(['watched' => true]);

        $this->postJson("/api/holidays/{$halloween->id}/clear-calendar")->assertOk()->assertJsonCount(5, 'data');

        $this->assertSame(array_fill(0, 5, 'month_away'), $this->windowsByRank($halloween));
        $this->assertSame(5, $halloween->movies()->count());
        $this->assertTrue($halloween->movies()->where('rank', 1)->firstOrFail()->watched);
        $this->assertSame(['day_of', 'day_of'], $this->windowsByRank($christmas));
    }

    public function test_slot_sizes_are_validated(): void
    {
        $holiday = Holiday::where('name', 'Halloween')->firstOrFail();

        $this->patchJson("/api/holidays/{$holiday->id}", ['day_of_count' => -1])->assertUnprocessable();
        $this->patchJson("/api/holidays/{$holiday->id}", ['week_of_count' => 'lots'])->assertUnprocessable();
    }
}
