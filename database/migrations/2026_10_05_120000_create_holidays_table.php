<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('emoji', 16)->default('🎬');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('holidays')->insert([
            ['name' => 'Halloween', 'emoji' => '🎃', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Christmas', 'emoji' => '🎄', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::table('movies', function (Blueprint $table) {
            $table->foreignId('holiday_id')->nullable()->after('decade')->constrained('holidays');
        });

        // Backfill from the old enum column, then drop it.
        DB::statement('UPDATE movies SET holiday_id = (SELECT id FROM holidays WHERE holidays.name = movies.holiday)');

        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('holiday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('holiday')->default('Halloween')->after('decade');
        });

        DB::statement('UPDATE movies SET holiday = (SELECT name FROM holidays WHERE holidays.id = movies.holiday_id)');

        Schema::table('movies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('holiday_id');
        });

        Schema::dropIfExists('holidays');
    }
};
