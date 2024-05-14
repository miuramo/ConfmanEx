<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogForbidden extends Model
{
    use HasFactory;

    protected $casts = [
        'request' => 'array',
    ];

    protected $fillable = [
        'uid',
        'url',
        'method',
        'mes',
        'request'
    ];

}
