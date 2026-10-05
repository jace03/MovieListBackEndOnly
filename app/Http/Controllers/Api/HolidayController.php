<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HolidayResource;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        return HolidayResource::collection(Holiday::orderBy('sort_order')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:holidays,name',
            'emoji' => 'nullable|string|max:16',
        ]);

        $holiday = Holiday::create([
            'name' => $validated['name'],
            'emoji' => $validated['emoji'] ?? '🎬',
            'sort_order' => (Holiday::max('sort_order') ?? 0) + 1,
        ]);

        return new HolidayResource($holiday);
    }
}
