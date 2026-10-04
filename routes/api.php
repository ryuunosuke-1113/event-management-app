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