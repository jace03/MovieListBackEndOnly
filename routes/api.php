<?php

use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\MovieController;
use Illuminate\Support\Facades\Route;

Route::apiResource('movies', MovieController::class);
Route::patch('movies/{movie}/toggle-watched', [MovieController::class, 'toggleWatched']);
Route::get('holidays', [HolidayController::class, 'index']);
Route::post('holidays', [HolidayController::class, 'store']);
