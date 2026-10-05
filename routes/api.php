<?php

use App\Http\Controllers\Api\MovieController;
use Illuminate\Support\Facades\Route;

Route::apiResource('movies', MovieController::class);
Route::patch('movies/{movie}/toggle-watched', [MovieController::class, 'toggleWatched']);
