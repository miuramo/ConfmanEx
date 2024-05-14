<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submit extends Model
{
    use HasFactory;

    protected $fillable = [
        'psessionid',
        'booth',
        'canceled',
        'award',
        'orderint',
    ];

    public function accept(){
        return $this->belongsTo(Accept::class);//逆はbelongsTo
    }

    public function paper(){
        return $this->belongsTo(Paper::class);
    }

    // public static function make()
    // {

    // }
    public function reviews()
    {
        return $this->hasMany(Review::class, "submit_id");
    }

}
