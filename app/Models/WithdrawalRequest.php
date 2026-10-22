<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'admin_note',
        'requested_at',
        'processed_at',
        'bank_name',
        'branch_name',
        'account_type',
        'account_number',
        'account_holder_name',
        'transfer_reference',
        'transfer_note',
        'paid_at',
    ];
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
            'paid_at' => 'datetime',

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