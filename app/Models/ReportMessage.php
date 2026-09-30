<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportMessage extends Model
{
    protected $fillable = [
        'user_report_id',
        'user_id',
        'body',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(
            UserReport::class,
            'user_report_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function attachments(): HasMany
    {
        return $this->hasMany(
            ReportMessageAttachment::class,
            'report_message_id'
        );
    }
}