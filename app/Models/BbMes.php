<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbMes extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bb_id',
        'subject',
        'mes',
    ];

    public function bb()
    {
        return $this->belongsTo(Bb::class, 'bb_id');
    }

}
