<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viewpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'orderint',
        'name',
        'desc',
        'content',
        'contentafter',
        'forrev',
        'formeta',
        'weight',
        'doReturn',
        'doReturnAcceptOnly',

    ];
}
