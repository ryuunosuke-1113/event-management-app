<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $memberships = $request->user()
            ->conversationMemberships()
            ->whereNull('archived_at')
            ->with([
                'conversation.event',
                'conversation.members.user.profile',
                'conversation.messages.user',
                'conversation.messages.reads',
            ])
            ->get()
            ->sort(function ($a, $b) use ($request) {
                $userId = $request->user()->id;

                $aUnreadCount = $a->conversation->messages
                    ->where('user_id', '!=', $userId)
                    ->filter(function ($message) use ($userId) {
                        return !$message->reads->contains('user_id', $userId);
                    })
                    ->count();

                $bUnreadCount = $b->conversation->messages
                    ->where('user_id', '!=', $userId)
                    ->filter(function ($message) use ($userId) {
                        return !$message->reads->contains('user_id', $userId);
                    })
                    ->count();

                // 未読ありを上へ
                if (($aUnreadCount > 0) !== ($bUnreadCount > 0)) {
                    return $aUnreadCount > 0 ? -1 : 1;
                }

                // 同じグループ内では最新メッセージが新しい順
                $aLatest = $a->conversation->messages->max('created_at')
                    ?? $a->conversation->created_at;

                $bLatest = $b->conversation->messages->max('created_at')
                    ?? $b->conversation->created_at;

                return $bLatest <=> $aLatest;
            })
            ->values();
        return view('chats.index', compact('memberships'));
    }
    public function archive(Request $request, Conversation $conversation)
    {
        $membership = $conversation->members()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $membership->archived_at = now();
        $membership->save();

        return redirect()
            ->route('chats.index')
            ->with('success', 'チャットをアーカイブしました。');
    }
    public function archived(Request $request)
    {
        $memberships = $request->user()
            ->conversationMemberships()
            ->whereNotNull('archived_at')
            ->with([
                'conversation.event',
                'conversation.members.user.profile',
                'conversation.messages.user',
                'conversation.messages.reads',
            ])
            ->latest('archived_at')
            ->get();

        return view('chats.archived', compact('memberships'));
    }
    public function restore(Request $request, Conversation $conversation)
    {
        $membership = $conversation->members()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $membership->archived_at = null;
        $membership->save();

        return redirect()
            ->route('chats.archived')
            ->with('success', 'チャットを再表示しました。');
    }
}