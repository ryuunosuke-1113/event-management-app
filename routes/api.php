<?php

use App\Models\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/events', function (Request $request) {
    $query = Event::query()
        ->with('images')
        ->where('status', 'published');

    if ($request->filled('keyword')) {
        $query->where(
            'title',
            'like',
            '%' . $request->keyword . '%'
        );
    }

    if ($request->filled('event_date')) {
        $query->whereDate(
            'event_date',
            $request->event_date
        );
    }

    return $query
        ->orderBy('event_date')
        ->get()
        ->map(function (Event $event) {
            $mainImage = $event->images->first();

            return [
                'id' => $event->id,
                'title' => $event->title,
                'event_date' => $event->event_date,
                'place' => $event->place,
                'price' => $event->price,
                'capacity' => $event->capacity,
                'image_url' => $mainImage
                    ? asset('storage/' . $mainImage->image_path)
                    : null,
            ];
        });
});
Route::get('/events/{event}', function (Event $event) {
    abort_unless($event->status === 'published', 404);

    $event->load('organizer:id,name');

    return response()->json([
        'id' => $event->id,
        'title' => $event->title,
        'event_date' => $event->event_date,
        'place' => $event->place,
        'price' => $event->price,
        'capacity' => $event->capacity,
        'description' => $event->description,
        'organizer' => $event->organizer
            ? [
                'id' => $event->organizer->id,
                'name' => $event->organizer->name,
            ]
            : null,
    ]);
})->whereNumber('event');