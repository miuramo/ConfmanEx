<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScheduledUpdate extends Model
{
    protected $fillable = [
        'target_type',
        'target_id',
        'field_name',
        'new_value',
        'status',
        'execute_at',
        'executed_at',
        'error_message',
    ];
    protected $casts = [
        'new_value' => 'array',
        'execute_at' => 'datetime',
        'executed_at' => 'datetime',
    ];
    public function target(): MorphTo
    {
        return $this->morphTo();
    }
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    public function scopeDue($query)
    {
        return $query->where('status', 'pending')->where('execute_at', '<=', now());
    }
}
