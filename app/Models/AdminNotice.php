<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotice extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'category',
        'title',
        'body',
        'event_id',
        'read_at',
        'user_moderation_action_id',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'admin_id'
        );
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'event_cancelled' => 'イベント中止',
            'moderation_applied' => 'アカウント制御',
            'moderation_updated' => 'アカウント制御変更',
            'moderation_lifted' => 'アカウント制御解除',
            'other' => '運営からのお知らせ',
            default => 'お知らせ',
        };
    }
    public function moderationAction(): BelongsTo
    {
        return $this->belongsTo(
            UserModerationAction::class,
            'user_moderation_action_id'
        );
    }
}