<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventParticipant extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'cancelled_at',
        'payment_expires_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
        'payment_expires_at' => 'datetime',
        'attended_at' => 'datetime',
    ];
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_payment' => '決済待ち',
            'confirmed' => '参加確定',
            'cancelled' => 'キャンセル済み',
            default => '不明',
        };
    }
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}