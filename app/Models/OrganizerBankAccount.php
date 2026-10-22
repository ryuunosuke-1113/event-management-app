<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizerBankAccount extends Model
{
    protected $fillable = [
        'user_id',
        'bank_name',
        'branch_name',
        'account_type',
        'account_number',
        'account_holder_name',
    ];
    protected function casts(): array
    {
        return [
            'bank_name' => 'encrypted',
            'branch_name' => 'encrypted',
            'account_type' => 'encrypted',
            'account_number' => 'encrypted',
            'account_holder_name' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}