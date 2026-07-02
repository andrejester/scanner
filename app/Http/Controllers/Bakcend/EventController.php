<?php

namespace App\Http\Controllers\Bakcend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    //
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'label' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'all_day' => 'boolean',
            'url' => 'nullable|url',
            'guests' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Simpan ke database
        $event = Event::create($request);

        return response()->json(['success' => true, 'event' => $event], 201);
    }
}
