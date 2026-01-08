<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedJob extends Model
{
    // 特に必要はないが、CRUDですこしでも操作できるように（選択コピーはuuidがユニークにならないのでできない）
    use HasFactory;
    public $timestamps = false;
}
