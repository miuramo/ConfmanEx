<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    // 特に必要はないが、CRUDですこしでも操作できるように
    use HasFactory;
    public $timestamps = false;
    //
    public $attributes = [
        'queue' => '',
        'payload' => '',
        'attempts' => 0,
        'reserved_at' => 1,
        'available_at' => 1,
        'created_at' => 1,
    ];
}
