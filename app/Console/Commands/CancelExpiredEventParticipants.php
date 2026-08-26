<?php

namespace App\Console\Commands;

use App\Models\EventParticipant;
use Illuminate\Console\Command;

class CancelExpiredEventParticipants extends Command
{
    protected $signature = 'event-participants:cancel-expired';

    protected $description = '支払い期限を過ぎた参加申し込みをキャンセルする';

    public function handle()
    {
        $participants = EventParticipant::where('status', 'pending_payment')
            ->whereNotNull('payment_expires_at')
            ->where('payment_expires_at', '<=', now())
            ->get();

        foreach ($participants as $participant) {
            $participant->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            if ($participant->payment && $participant->payment->status === 'pending') {
                $participant->payment->update([
                    'status' => 'failed',
                ]);
            }
        }

        $this->info($participants->count() . '件をキャンセルしました。');

        return Command::SUCCESS;
    }
}