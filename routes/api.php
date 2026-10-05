<?php

use App\Models\Event;
use Illuminate\Support\Facades\Route;

Route::get('/events', function () {
    return Event::query()
        ->where('status', 'published')
        ->orderBy('event_date')
        ->get([
            'id',
            'title',
            'event_date',
            'place',
            'price',
        ]);
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