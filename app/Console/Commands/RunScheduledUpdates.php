<?php

namespace App\Console\Commands;

use App\Jobs\RunScheduledUpdate;
use App\Models\ScheduledUpdate;
use Illuminate\Console\Command;

class RunScheduledUpdates extends Command
{
    protected $signature = 'scheduled-updates:run';
    protected $description = 'Run due scheduled updates';
    public function handle(): int
    {
        ScheduledUpdate::due()->orderBy('execute_at')->chunkById(100, function ($scheduledUpdates) {
            foreach ($scheduledUpdates as $scheduledUpdate) {
                RunScheduledUpdate::dispatch($scheduledUpdate->id);
            }
        });
        $this->info('Scheduled updates dispatched.');
        return self::SUCCESS;
    }
}
