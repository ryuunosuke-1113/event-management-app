<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserReport extends Model
{
    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reason',
        'details',
        'status',
        'handled_by',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reporter_id'
        );
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reported_user_id'
        );
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'handled_by'
        );
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => '未対応',
            'in_progress' => '対応中',
            'resolved' => '解決済み',
            default => '不明',
        };
    }
    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'nuisance' => '迷惑行為',
            'inappropriate' => '不適切な発言・行動',
            'impersonation' => '虚偽・なりすまし',
            'event_trouble' => 'イベント上のトラブル',
            'other' => 'その他',
            default => '不明',
        };
    }
    public function messages(): HasMany
    {
        return $this->hasMany(
            ReportMessage::class,
            'user_report_id'
        );
    }
}