<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EventParticipant;

class DirectChatRelation extends Model
{
    protected $fillable = [
        'user_id',
        'related_user_id',
    ];
    public static function createRelation(int $firstUserId, int $secondUserId): void
    {
        if ($firstUserId === $secondUserId) {
            return;
        }

        $userId = min($firstUserId, $secondUserId);
        $relatedUserId = max($firstUserId, $secondUserId);

        self::firstOrCreate([
            'user_id' => $userId,
            'related_user_id' => $relatedUserId,
        ]);
    }
    public static function createForConfirmedParticipant(
        EventParticipant $eventParticipant
    ): void {
        $event = $eventParticipant->event;

        // 主催者 ↔ 今回参加確定したユーザー
        self::createRelation(
            $event->organizer_id,
            $eventParticipant->user_id
        );

        // 今回参加確定したユーザー ↔
        // すでに参加確定している他の参加者
        $confirmedParticipants = $event->participants()
            ->where('status', 'confirmed')
            ->where('user_id', '!=', $eventParticipant->user_id)
            ->get();

        foreach ($confirmedParticipants as $confirmedParticipant) {
            self::createRelation(
                $eventParticipant->user_id,
                $confirmedParticipant->user_id
            );
        }
    }
}