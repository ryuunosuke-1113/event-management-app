<?php

namespace App\Http\Controllers;

use App\Models\Event;

class PublicEventController extends Controller
{
    public function index()
    {
        $events = Event::where('status', 'published')
            ->orderBy('event_date')
            ->get();

        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        abort_unless($event->status === 'published', 404);

        $event->load([
            'participants' => function ($query) {
                $query->where('status', 'confirmed');
            },
            'participants.user.profile',
            'conversations' => function ($query) {
                $query->where('type', 'event');
            },
            'conversations.members',
        ]);
        $occupiedCount = $event->participants()
            ->where('status', 'confirmed')
            ->count();

        $isFull = $occupiedCount >= $event->capacity;

        return view('events.show', compact(
            'event',
            'occupiedCount',
            'isFull'
        ));
    }
}