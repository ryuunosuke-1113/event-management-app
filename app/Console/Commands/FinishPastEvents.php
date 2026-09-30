<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class FinishPastEvents extends Command
{
    protected $signature = 'events:finish-past';

    protected $description = '開催日時を過ぎたイベントを開催終了にする';

    public function handle(): int
    {
        $count = Event::query()
            ->whereIn('status', [
                'published',
                'closed',
            ])
            ->where('event_date', '<', now())
            ->update([
                'status' => 'finished',
            ]);

        $this->info(
            "{$count}件のイベントを開催終了にしました。"
        );

        return self::SUCCESS;
    }
}