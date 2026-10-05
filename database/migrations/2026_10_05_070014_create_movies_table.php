<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedSmallInteger('year')->nullable();
            $table->enum('added_by', ['His', 'Hers', 'Both'])->default('Both');
            $table->unsignedTinyInteger('rating')->default(0);
            $table->string('genre')->nullable();
            $table->string('decade')->nullable();
            $table->integer('rank')->nullable();
            $table->boolean('watched')->default(false);
            $table->text('notes')->nullable();
            $table->string('poster_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
