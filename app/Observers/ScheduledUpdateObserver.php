<?php

namespace App\Observers;

use App\Jobs\RunScheduledUpdate;
use App\Models\LogCreate;
use App\Models\LogModify;
use App\Models\ScheduledUpdate;
use Illuminate\Support\Facades\Auth;

class ScheduledUpdateObserver
{
    public function updating(ScheduledUpdate $scheduledUpdate): void
    {
        $current = ScheduledUpdate::find($scheduledUpdate->id);
        $diffary = [];
        foreach ($scheduledUpdate->getDirty() as $field => $newval) {
            if (isset($current) && $current->{$field} != $newval) {
                if (is_array($newval)) {
                    $newval = json_encode($newval, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                $currentval = $current->{$field};
                if (is_array($currentval)) {
                    $currentval = json_encode($currentval, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                $diffary[] = "{$field} : {$currentval} → {$newval}";
            }
        }

        LogModify::create([
            'uid' => Auth::id() ?? 0,
            'table' => 'scheduled_updates',
            'target_id' => $scheduledUpdate->id,
            'diff' => implode("\n", $diffary),
        ]);
    }

    public function created(ScheduledUpdate $scheduledUpdate): void
    {
        LogCreate::create([
            'uid' => Auth::id() ?? 0,
            'table' => 'scheduled_updates',
            'target_id' => $scheduledUpdate->id,
            'data' => $scheduledUpdate,
        ]);

        $this->dispatchPendingJob($scheduledUpdate);
    }

    public function updated(ScheduledUpdate $scheduledUpdate): void
    {
        if ($scheduledUpdate->wasChanged(['target_type', 'target_id', 'field_name', 'new_value', 'execute_at', 'status'])) {
            $this->dispatchPendingJob($scheduledUpdate);
        }
    }

    public function deleted(ScheduledUpdate $scheduledUpdate): void
    {
        LogCreate::create([
            'uid' => Auth::id() ?? 0,
            'table' => 'scheduled_updates',
            'target_id' => $scheduledUpdate->id,
            'data' => '{"deleted":"deleted"}',
        ]);
    }

    private function dispatchPendingJob(ScheduledUpdate $scheduledUpdate): void
    {
        if ($scheduledUpdate->status !== 'pending' || $scheduledUpdate->execute_at === null) {
            return;
        }

        RunScheduledUpdate::dispatch($scheduledUpdate->id, $scheduledUpdate->execute_at->toDateTimeString())
            ->delay($scheduledUpdate->execute_at);
    }
}
