<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationMessage extends Model
{
    protected $fillable = [
        'user_moderation_action_id',
        'user_id',
        'body',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function moderationAction(): BelongsTo
    {
        return $this->belongsTo(
            UserModerationAction::class,
            'user_moderation_action_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}