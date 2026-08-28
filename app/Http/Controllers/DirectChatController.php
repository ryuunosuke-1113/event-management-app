<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DirectChatRelation;
use App\Events\MessageSent;
use App\Events\MessageReadUpdated;

class DirectChatController extends Controller
{
    public function start(Request $request, User $user)
    {

        $currentUser = $request->user();

        if ($currentUser->id === $user->id) {
            abort(403);
        }
        $userId = min($currentUser->id, $user->id);
        $relatedUserId = max($currentUser->id, $user->id);

        $hasRelation = DirectChatRelation::where('user_id', $userId)
            ->where('related_user_id', $relatedUserId)
            ->exists();

        $eventId = $request->input('event_id');

        $isOrganizerContact = false;

        if ($eventId) {
            $event = Event::find($eventId);

            $isOrganizerContact =
                $event &&
                $event->organizer_id === $user->id;
        }

        if (!$hasRelation && !$isOrganizerContact) {
            abort(403);
        }
        $conversation = Conversation::where('type', 'direct')
            ->whereHas('members', function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })
            ->whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->withCount('members')
            ->get()
            ->firstWhere('members_count', 2);

        if (!$conversation) {
            $conversation = DB::transaction(function () use ($currentUser, $user) {
                $conversation = Conversation::create([
                    'event_id' => null,
                    'type' => 'direct',
                ]);

                $conversation->members()->createMany([
                    [
                        'user_id' => $currentUser->id,
                    ],
                    [
                        'user_id' => $user->id,
                    ],
                ]);

                return $conversation;
            });
        }

        return redirect()->route('direct-chat.show', $conversation);
    }
    public function show(Request $request, Conversation $conversation)
    {
        if ($conversation->type !== 'direct') {
            abort(404);
        }

        $isMember = $conversation->members()
            ->where('user_id', $request->user()->id)
            ->exists();

        if (!$isMember) {
            abort(403);
        }

        $conversation->load([
            'event',
            'members.user.profile',
            'messages.user.profile',
            'messages.reads',
        ]);
        foreach ($conversation->messages as $message) {
            if ($message->user_id === $request->user()->id) {
                continue;
            }

            $read = $message->reads()->firstOrCreate(
                [
                    'user_id' => $request->user()->id,
                ],
                [
                    'read_at' => now(),
                ]
            );

            if ($read->wasRecentlyCreated) {
                MessageReadUpdated::dispatch($message);
            }
        }

        return view('direct-chat.show', compact('conversation'));
    }
    public function storeMessage(
        Request $request,
        Conversation $conversation
    ) {
        if ($conversation->type !== 'direct') {
            abort(404);
        }

        $isMember = $conversation->members()
            ->where('user_id', $request->user()->id)
            ->exists();

        if (!$isMember) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        MessageSent::dispatch($message);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message_id' => $message->id,
            ]);
        }

        return redirect()
            ->route('direct-chat.show', $conversation);
    }
}