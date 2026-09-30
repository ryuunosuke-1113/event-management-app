<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportMessageAttachment extends Model
{
    protected $fillable = [
        'report_message_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(
            ReportMessage::class,
            'report_message_id'
        );
    }
}