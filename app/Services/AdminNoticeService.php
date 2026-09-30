<?php

namespace App\Services;

use App\Models\AdminNotice;
use App\Models\Event;
use App\Models\User;
use App\Models\UserModerationAction;

class AdminNoticeService
{
    public function sendModerationApplied(
        User $user,
        ?User $admin,
        UserModerationAction $action,
        string $title,
        string $body
    ): AdminNotice {
        return AdminNotice::create([
            'user_id' => $user->id,
            'admin_id' => $admin?->id,
            'user_moderation_action_id' => $action->id,
            'category' => 'moderation_applied',
            'title' => $title,
            'body' => $body,
        ]);
    }
    public function sendModerationUpdated(
        User $user,
        ?User $admin,
        UserModerationAction $action,
        string $title,
        string $body
    ): AdminNotice {
        return AdminNotice::create([
            'user_id' => $user->id,
            'admin_id' => $admin?->id,
            'user_moderation_action_id' => $action->id,
            'category' => 'moderation_updated',
            'title' => $title,
            'body' => $body,
        ]);
    }
    public function sendModerationLifted(
        User $user,
        ?User $admin,
        string $body
    ): AdminNotice {
        return AdminNotice::create([
            'user_id' => $user->id,
            'admin_id' => $admin?->id,
            'category' => 'moderation_lifted',
            'title' => 'アカウント制御解除のお知らせ',
            'body' => $body,
        ]);
    }

    public function sendEventCancelled(
        User $user,
        ?User $admin,
        Event $event,
        string $body
    ): AdminNotice {
        return AdminNotice::create([
            'user_id' => $user->id,
            'admin_id' => $admin?->id,
            'category' => 'event_cancelled',
            'title' => 'イベント中止のお知らせ',
            'body' => $body,
            'event_id' => $event->id,
        ]);
    }
}