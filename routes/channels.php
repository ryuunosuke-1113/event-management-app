<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    return Conversation::whereKey($conversationId)
        ->whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->exists();
});