<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMember extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'archived_at',
    ];
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }
}