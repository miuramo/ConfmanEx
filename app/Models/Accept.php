<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accept extends Model
{
    use HasFactory;

    public function submits(){
        return $this->belongsTo(Submit::class); //逆はhasOne
    }
}
