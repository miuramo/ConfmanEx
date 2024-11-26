<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnotPaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'paper_id',
        'user_id',
        'file_id',
    ];

    protected $with = ['annots', 'file' , 'paper', 'user'];

    public function annots()
    {
        return $this->hasMany(Annot::class);
    }
    public function file()
    {
        return $this->belongsTo(File::class);
    }
    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
