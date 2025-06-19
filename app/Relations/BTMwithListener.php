<?php

namespace App\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Closure;

class BTMwithListener extends BelongsToMany
{
    /** @var Closure|null 共通リスナー（attach/detach/sync） */
    protected ?Closure $listener = null;

    /**
     * リスナー登録
     *
     * @param Closure $callback function($operation, $parent, $related, $relatedId)
     * @return $this
     */
    public function withListener(Closure $callback): static
    {
        $this->listener = $callback;
        return $this;
    }
    protected function notifyListener(string $operation, $relatedId): void
    {
        if ($this->listener) {
            ($this->listener)($operation, $this->parent, $this->related, $relatedId);
        }
    }

    public function attach($id, array $attributes = [], $touch = true)
    {
        parent::attach($id, $attributes, $touch);

        if (is_array($id)) {
            foreach ($id as $key => $value) {
                $relatedId = is_int($key) ? $value : $key;
                $this->notifyListener('attach', $relatedId);
            }
        } else {
            $this->notifyListener('attach', $id);
        }
        // Log::info("Attached {$this->related->getTable()} ID {$i} to {$this->parent->getTable()} ID {$this->parent->getKey()}");
    }

    public function detach($ids = null, $touch = true)
    {
        $detached = parent::detach($ids, $touch);

        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->notifyListener('detach', $id);
            }
        } elseif (!is_null($ids)) {
            $this->notifyListener('detach', $ids);
        }
        // Log::info("Detached {$this->related->getTable()} ID {$i} from {$this->parent->getTable()} ID {$this->parent->getKey()}");


        return $detached;
    }

    public function syncWithoutDetaching($ids, $touch = true)
    {
        $pivotTable = $this->table;
        $foreign = $this->foreignPivotKey;
        $related = $this->relatedPivotKey;

        // 呼び出し形式が [id1, id2, ...] または [id => attributes, ...] の両方に対応
        $normalizedIds = is_array($ids)
            ? (array_keys($ids) === range(0, count($ids) - 1) ? $ids : array_keys($ids))
            : (array) $ids;

        $before = DB::table($pivotTable)
            ->where($foreign, $this->parent->getKey())
            ->pluck($related)
            ->toArray();

        parent::syncWithoutDetaching($ids, $touch);

        $after = DB::table($pivotTable)
            ->where($foreign, $this->parent->getKey())
            ->pluck($related)
            ->toArray();

        $added = array_diff($after, $before);

        foreach ($added as $i) {
            $this->notifyListener('sync', $i);
            // Log::info("SyncWithoutDetaching attached {$this->related->getTable()} ID {$i} to {$this->parent->getTable()} ID {$this->parent->getKey()}");
        }

        return $added;
    }
}
