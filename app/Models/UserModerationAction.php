<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserModerationAction extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'action_type',
        'comment',
        'is_active',
        'ended_at',
        'ended_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ended_at' => 'datetime',
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

    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'ended_by'
        );
    }

    public function getActionTypeLabelAttribute(): string
    {
        return match ($this->action_type) {
            'warning' => '警告',
            'suspension' => 'アカウント停止',
            default => '不明',
        };
    }
    public function messages(): HasMany
    {
        return $this->hasMany(
            ModerationMessage::class,
            'user_moderation_action_id'
        );
    }
}