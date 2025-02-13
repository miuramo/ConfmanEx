<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnqueteConfig extends Model
{
    use HasFactory;


    public function isopen()
    {
        return Enquete::checkdayduration($this->openstart, $this->openend);
    }
}
