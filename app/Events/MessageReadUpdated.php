<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReadUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Message $message
    ) {
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
        return 'message.read-updated';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'read_count' => $this->message->reads()->count(),
        ];
    }
}