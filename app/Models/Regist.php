<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Regist extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'valid',
        'paid',
        'paid_at',
        'payment_method',
        'payment_id',
        'payment_status',
        'confirmed_at',
    ];
    //
}
