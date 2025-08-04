<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteTicket extends Model
{
    use HasFactory;
    
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

    // protected $attributes = [
    //     'email' => 'test@example.com',
    // ];

    public function url()
    {
        return route('vote.activate', ['token' => $this->token]);
    }
}
