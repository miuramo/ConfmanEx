<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAccess extends Model
{
    use HasFactory;

    protected $casts = [
        'request' => 'json',
    ];
    protected $fillable = [
        'uid',
        'url',
        'method',
        'request'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'uid');
    }
    /**
     * App\Http\Middleware\LogAccess で、実際のアクセスログ保存処理を行っている。
     */
}
