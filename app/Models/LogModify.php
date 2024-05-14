<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogModify extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'table',
        'target_id',
        'diff',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uid');
    }
}
