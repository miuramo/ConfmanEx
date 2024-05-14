<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'submitted',
        'valid',
        'paid',
        'early',
        'memo',
    ];

    public function event(){
        return $this->hasOne(Event::class);
    }

    public function user(){
        return $this->hasOne(User::class);
    }

}
