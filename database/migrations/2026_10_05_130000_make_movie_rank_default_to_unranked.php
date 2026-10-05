<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rank 1 is the top of a holiday's list; 100 means unranked (sorts last).
     */
    public function up(): void
    {
        // Renumber each holiday's ranked movies 1..N so no two share a rank.
        $ranked = DB::table('movies')
            ->whereNotNull('rank')
            ->where('rank', '<', 100)
            ->orderBy('rank')
            ->orderBy('id')
            ->get(['id', 'holiday_id'])
            ->groupBy('holiday_id');

        foreach ($ranked as $movies) {
            foreach ($movies->values() as $index => $movie) {
                DB::table('movies')->where('id', $movie->id)->update(['rank' => $index + 1]);
            }
        }

        DB::table('movies')->where(fn ($query) => $query->whereNull('rank')->orWhere('rank', '>=', 100))->update(['rank' => 100]);

        Schema::table('movies', function (Blueprint $table) {
            $table->integer('rank')->default(100)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->integer('rank')->nullable()->default(null)->change();
        });

        DB::table('movies')->where('rank', 100)->update(['rank' => null]);
    }
};
