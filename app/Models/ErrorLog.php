<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'level',
        'message',
        'context',
        'extra',
        'logged_at',
    ];

    protected $casts = [
        'context' => 'array',
        'extra'   => 'array',
    ];
    //
}
