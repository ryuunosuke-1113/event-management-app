<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Message $message
    ) {
        $this->message->load('user.profile');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'conversation.' . $this->message->conversation_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'body' => $this->message->body,
            'created_at' => $this->message->created_at
                ->format('Y/m/d H:i'),

            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
                'photo_url' => $this->message->user->profile?->photo_path
                    ? asset(
                        'storage/' .
                        $this->message->user->profile->photo_path
                    )
                    : null,
            ],
        ];
    }
}