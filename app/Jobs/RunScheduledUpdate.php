<?php

namespace App\Jobs;

use App\Models\ScheduledUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class RunScheduledUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public int $scheduledUpdateId) {}
    public function handle(): void
    {
        $scheduledUpdate = ScheduledUpdate::with('target')->find($this->scheduledUpdateId);
        if (! $scheduledUpdate) {
            return;
        }
        if ($scheduledUpdate->status !== 'pending') {
            return;
        }
        $target = $scheduledUpdate->target;
        if (! $target) {
            $scheduledUpdate->update(['status' => 'failed', 'executed_at' => now(), 'error_message' => 'Target model not found.',]);
            return;
        }
        try {
            $target->{$scheduledUpdate->field_name} = $scheduledUpdate->new_value[$scheduledUpdate->field_name] ?? $scheduledUpdate->new_value;
            $target->save();
            $scheduledUpdate->update(['status' => 'completed', 'executed_at' => now(), 'error_message' => null,]);
        } catch (Throwable $e) {
            $scheduledUpdate->update(['status' => 'failed', 'executed_at' => now(), 'error_message' => $e->getMessage(),]);
            throw $e;
        }
    }
}
