<?php

use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\MovieController;
use Illuminate\Support\Facades\Route;

Route::apiResource('movies', MovieController::class);
Route::patch('movies/{movie}/toggle-watched', [MovieController::class, 'toggleWatched']);
Route::get('holidays', [HolidayController::class, 'index']);
Route::post('holidays', [HolidayController::class, 'store']);
Route::patch('holidays/{holiday}', [HolidayController::class, 'update']);
Route::post('holidays/{holiday}/auto-calculate-calendar', [HolidayController::class, 'autoCalculateCalendar']);
Route::post('holidays/{holiday}/clear-calendar', [HolidayController::class, 'clearCalendar']);
