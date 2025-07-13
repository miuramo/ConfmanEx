<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoteTicket extends Model
{
    protected $fillable = [
        'email',
        'token',
        'token_hash',
        'user_id',
        'valid',
        'activated',
        'submitted_at',
    ];
    //
}
