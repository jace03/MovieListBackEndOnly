<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `watched` already exists on movies, so only the calendar slot is added here.
     * Existing movies land in 'month_away' (the "rest of them" slot).
     */
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('watch_window', 32)->default('month_away')->after('rank');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('watch_window');
        });
    }
};
