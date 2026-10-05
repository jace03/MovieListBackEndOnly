<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brings the original `movieslist` database (movies with a plain `holiday`
 * string and a `description` column) up to the schema the API expects:
 * holidays table, holiday_id, year, added_by, rank, watched, notes, poster_url.
 *
 * The earlier create_movies / create_holidays migrations are for an empty
 * database; on `movieslist` they are recorded as already run and this one does
 * the in-place conversion instead. It is a no-op on a freshly migrated database.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('movies', 'description') && ! Schema::hasColumn('movies', 'holiday')) {
            return;
        }

        if (! Schema::hasTable('holidays')) {
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
        }

        // MyISAM cannot hold the holiday foreign key.
        DB::statement('ALTER TABLE movies ENGINE=InnoDB');

        Schema::table('movies', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->nullable()->after('title');
            $table->enum('added_by', ['His', 'Hers', 'Both'])->default('Both')->after('year');
            $table->string('genre')->nullable()->change();
            $table->string('decade')->nullable()->change();
            $table->foreignId('holiday_id')->nullable()->after('decade')->constrained('holidays');
            $table->integer('rank')->nullable()->after('holiday_id');
            $table->boolean('watched')->default(false)->after('rank');
            $table->text('notes')->nullable()->after('watched');
            $table->string('poster_url')->nullable()->after('notes');
        });

        DB::statement('UPDATE movies SET holiday_id = (SELECT id FROM holidays WHERE holidays.name = movies.holiday)');
        DB::statement('UPDATE movies SET notes = description');

        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['holiday', 'description']);
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('holiday')->nullable()->after('decade');
            $table->text('description')->nullable()->after('decade');
        });

        DB::statement('UPDATE movies SET holiday = (SELECT name FROM holidays WHERE holidays.id = movies.holiday_id)');
        DB::statement('UPDATE movies SET description = notes');

        Schema::table('movies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('holiday_id');
            $table->dropColumn(['year', 'added_by', 'rank', 'watched', 'notes', 'poster_url']);
        });

        Schema::dropIfExists('holidays');
    }
};
