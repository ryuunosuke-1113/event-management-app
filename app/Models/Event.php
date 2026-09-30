<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'place',
        'capacity',
        'price',
        'status',
        'chat_url',
        'cancel_policy',
        'organizer_id',
        'archived_at',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => '下書き',
            'published' => '公開中',
            'closed' => '募集終了',
            'finished' => '開催終了',
            'cancelled' => 'イベント中止',
            default => '不明',
        };
    }
    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }
    public function refundRateAt(CarbonInterface $cancelledAt): int
    {
        $eventDate = $this->event_date->copy()->startOfDay();
        $cancelDate = $cancelledAt->copy()->startOfDay();

        if ($cancelDate->lte($eventDate->copy()->subDays(3))) {
            return (int) config(
                'event.cancel_policy.participant.three_days_before'
            );
        }

        if ($cancelDate->lt($eventDate)) {
            return (int) config(
                'event.cancel_policy.participant.two_days_to_day_before'
            );
        }

        return (int) config(
            'event.cancel_policy.participant.event_day'
        );
    }
    public function refundAmountAt(
        CarbonInterface $cancelledAt,
        int $paidAmount
    ): int {
        $rate = $this->refundRateAt($cancelledAt);

        return (int) floor($paidAmount * $rate / 100);
    }
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

}