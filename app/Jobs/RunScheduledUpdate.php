<?php

namespace App\Jobs;

use App\Models\ScheduledUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RunScheduledUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(
        public int $scheduledUpdateId,
        public ?string $expectedExecuteAt = null,
    ) {}

    public function handle(): void
    {
        Log::info('RunScheduledUpdate started.', [
            'scheduled_update_id' => $this->scheduledUpdateId,
            'expected_execute_at' => $this->expectedExecuteAt,
        ]);

        $scheduledUpdate = ScheduledUpdate::with('target')->find($this->scheduledUpdateId);
        if (! $scheduledUpdate) {
            Log::warning('RunScheduledUpdate skipped: scheduled update not found.', [
                'scheduled_update_id' => $this->scheduledUpdateId,
            ]);
            return;
        }
        if ($scheduledUpdate->status !== 'pending') {
            Log::info('RunScheduledUpdate skipped: status is not pending.', [
                'scheduled_update_id' => $scheduledUpdate->id,
                'status' => $scheduledUpdate->status,
            ]);
            return;
        }
        if ($this->expectedExecuteAt !== null && $scheduledUpdate->execute_at->toDateTimeString() !== $this->expectedExecuteAt) {
            Log::info('RunScheduledUpdate skipped: execute_at was changed.', [
                'scheduled_update_id' => $scheduledUpdate->id,
                'expected_execute_at' => $this->expectedExecuteAt,
                'current_execute_at' => $scheduledUpdate->execute_at->toDateTimeString(),
            ]);
            return;
        }
        if ($scheduledUpdate->execute_at->isFuture()) {
            $delaySeconds = (int) now()->diffInSeconds($scheduledUpdate->execute_at);
            Log::info('RunScheduledUpdate released: execute_at is still future.', [
                'scheduled_update_id' => $scheduledUpdate->id,
                'execute_at' => $scheduledUpdate->execute_at->toDateTimeString(),
                'delay_seconds' => $delaySeconds,
            ]);
            $this->release($delaySeconds);
            return;
        }
        $target = $scheduledUpdate->target;
        if (! $target) {
            Log::warning('RunScheduledUpdate failed: target model not found.', [
                'scheduled_update_id' => $scheduledUpdate->id,
                'target_type' => $scheduledUpdate->target_type,
                'target_id' => $scheduledUpdate->target_id,
            ]);
            $scheduledUpdate->update(['status' => 'failed', 'executed_at' => now(), 'error_message' => 'Target model not found.',]);
            return;
        }
        try {
            $target->{$scheduledUpdate->field_name} = $scheduledUpdate->new_value[$scheduledUpdate->field_name] ?? $scheduledUpdate->new_value;
            $target->save();
            $scheduledUpdate->update(['status' => 'completed', 'executed_at' => now(), 'error_message' => null,]);
            Log::info('RunScheduledUpdate completed.', [
                'scheduled_update_id' => $scheduledUpdate->id,
                'target_type' => $scheduledUpdate->target_type,
                'target_id' => $scheduledUpdate->target_id,
                'field_name' => $scheduledUpdate->field_name,
            ]);
        } catch (Throwable $e) {
            $scheduledUpdate->update(['status' => 'failed', 'executed_at' => now(), 'error_message' => $e->getMessage(),]);
            Log::error('RunScheduledUpdate failed with exception.', [
                'scheduled_update_id' => $scheduledUpdate->id,
                'target_type' => $scheduledUpdate->target_type,
                'target_id' => $scheduledUpdate->target_id,
                'field_name' => $scheduledUpdate->field_name,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
