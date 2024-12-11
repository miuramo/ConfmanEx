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
        return $this->hasMany(Annot::class)->whereRaw('CHAR_LENGTH(content) > ?', [82]);
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

    public function get_fabric_objects($page = 1)
    {
        $annots = $this->annots->where('page', $page);
        $concat = [];
        foreach($annots as $eachannot){
            $objcontent = json_decode($eachannot->content, true);
            foreach($objcontent['objects'] as $ooo){
                $ooo['user_id'] = $eachannot->user_id;
                $ooo['name'] = $eachannot->user->name;
                $ooo['affil'] = $eachannot->user->affil;
                $concat[] = $ooo;
            }
        }
        $objects = [];
        $objects['version'] = '6.5.1';
        $objects['objects'] = $concat;
        $final = json_encode($objects);
        return $final;
    }

}
