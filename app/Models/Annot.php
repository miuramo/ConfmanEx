<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annot extends Model
{
    use HasFactory;

    protected $fillable = [
        'annot_paper_id',
        'paper_id',
        'page',
        'content',
        'user_id',
        'iine',
    ];

    protected $with = ['user', 'annot_paper', 'paper'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function annot_paper()
    {
        return $this->belongsTo(AnnotPaper::class);
    }
    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }


}
