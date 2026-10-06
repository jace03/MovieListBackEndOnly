<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How many movies auto-calculate puts in each calendar slot; the rest go to 'month_away'.
     */
    public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->unsignedSmallInteger('day_of_count')->default(3)->after('sort_order');
            $table->unsignedSmallInteger('week_of_count')->default(7)->after('day_of_count');
            $table->unsignedSmallInteger('two_weeks_away_count')->default(4)->after('week_of_count');
            $table->unsignedSmallInteger('three_weeks_away_count')->default(4)->after('two_weeks_away_count');
        });
    }

    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn(['day_of_count', 'week_of_count', 'two_weeks_away_count', 'three_weeks_away_count']);
        });
    }
};
