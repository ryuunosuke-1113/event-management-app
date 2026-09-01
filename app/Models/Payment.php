<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'event_participant_id',
        'amount',
        'payment_method',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'status',
        'paid_at',
        'refunded_at',
        'refunded_amount',
        'refund_status',
        'refund_due_amount',
    ];
    protected $casts = [
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function eventParticipant(): BelongsTo
    {
        return $this->belongsTo(EventParticipant::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => '決済待ち',
            'paid' => '支払い済み',
            'failed' => '決済失敗',
            'refunded' => '返金済み',
            default => '不明',
        };
    }
    public function getDisplayStatusLabelAttribute(): string
    {
        if ($this->payment_method === 'online') {
            return match ($this->status) {
                'pending' => 'オンライン決済確認待ち',
                'paid' => 'オンライン決済済み',
                'failed' => 'オンライン決済失敗',
                'refunded' => '返金済み',
                default => '不明',
            };
        }

        return match ($this->status) {
            'pending' => 'Stripe決済待ち',
            'paid' => 'Stripe支払い済み',
            'failed' => 'Stripe決済失敗',
            'refunded' => '返金済み',
            default => '不明',
        };
    }
    public function getRefundStatusLabelAttribute(): string
    {
        return match ($this->refund_status) {
            'pending' => '返金待ち',
            'completed' => '返金済み',
            'not_required' => '返金不要',
            default => '－',
        };
    }
}