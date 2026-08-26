<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'body',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function getBodyHtmlAttribute(): string
    {
        $escapedBody = e($this->body);

        return preg_replace_callback(
            '/https?:\/\/[^\s<]+/',
            function ($matches) {
                $url = $matches[0];

                return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">'
                    . $url
                    . '</a>';
            },
            $escapedBody
        );
    }
    public function reads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }
}