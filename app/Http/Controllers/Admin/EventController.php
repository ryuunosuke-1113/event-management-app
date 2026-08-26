<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('event_date')->get();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_date' => ['required', 'date'],
            'place' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published,closed,finished,cancelled'],
            'chat_url' => ['nullable', 'url'],
            'cancel_policy' => ['nullable', 'string'],

        ]);
        $validated['organizer_id'] = $request->user()->id;

        $event = Event::create($validated);

        $conversation = $event->conversations()->firstOrCreate([
            'type' => 'event',
        ]);

        $conversation->members()->firstOrCreate([
            'user_id' => $event->organizer_id,
        ]);
        return redirect()
            ->route('admin.events.index')
            ->with('success', 'イベントを作成しました。');
    }
    public function show(Event $event)
    {
        $event->load([
            'participants.user',
            'participants.payment',
        ]);

        return view('admin.events.show', compact('event'));
    }
    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_date' => ['required', 'date'],
            'place' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published,closed,finished,cancelled'],
            'chat_url' => ['nullable', 'url'],
            'cancel_policy' => ['nullable', 'string'],
        ]);

        $event->update($validated);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'イベントを更新しました。');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'イベントを削除しました。');
    }
}