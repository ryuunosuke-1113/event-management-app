<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Events\MessageReadUpdated;
use App\Models\Message;


class EventChatController extends Controller
{
    public function show(Request $request, Event $event)
    {
        $conversation = $event->conversations()
            ->where('type', 'event')
            ->firstOrFail();

        $isMember = $conversation->members()
            ->where('user_id', $request->user()->id)
            ->exists();

        if (!$isMember) {
            abort(403);
        }

        $conversation->load([
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
        return view('event-chat.show', compact(
            'event',
            'conversation'
        ));
    }
    public function storeMessage(Request $request, Event $event)
    {
        $conversation = $event->conversations()
            ->where('type', 'event')
            ->firstOrFail();

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
            ->route('event-chat.show', $event)
            ->with('success', 'メッセージを送信しました。');
    }
    public function markAsRead(Request $request, Message $message)
    {
        $user = $request->user();

        $isMember = $message->conversation
            ->members()
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            abort(403);
        }

        if ($message->user_id === $user->id) {
            return response()->json([
                'message' => '自分のメッセージは既読登録しません。',
            ]);
        }

        $read = $message->reads()->firstOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'read_at' => now(),
            ]
        );

        if ($read->wasRecentlyCreated) {
            MessageReadUpdated::dispatch($message);
        }

        return response()->json([
            'read' => true,
        ]);
    }
    public function archive(Request $request, Event $event)
    {
        $conversation = $event->conversations()
            ->where('type', 'event')
            ->firstOrFail();

        $membership = $conversation->members()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $membership->update([
            'archived_at' => now(),
        ]);

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'チャットをアーカイブしました。');
    }
    public function archived(Request $request)
    {
        $memberships = $request->user()
            ->conversationMemberships()
            ->whereNotNull('archived_at')
            ->with('conversation.event')
            ->latest('archived_at')
            ->get();

        return view('event-chat.archived', compact('memberships'));
    }
    public function restore(Request $request, Event $event)
    {
        $conversation = $event->conversations()
            ->where('type', 'event')
            ->firstOrFail();

        $membership = $conversation->members()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $membership->archived_at = null;
        $membership->save();

        return redirect()
            ->route('event-chat.archived')
            ->with('success', 'チャットを再表示しました。');
    }
}