<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BbMes extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bb_id',
        'subject',
        'mes',
    ];

    protected $with = ['bb', 'files'];

    public function bb()
    {
        return $this->belongsTo(Bb::class, 'bb_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'bb_mes_id');
    }

    public static function whois(BbMes $mes)
    {
        $bb = $mes->bb;
        $paper = $bb->paper;
        $email = User::find($mes->user_id)->email ?? "";
        if ($mes->user_id == $paper->owner) {
            return "（投稿者）";
        } else if ($paper->isCoAuthorEmail($email)) {
            return "（共著者）";
        } else if ($mes->user_id == 0) {
            return "（システム）";
        } else {
            return "（委員）";
        }
    }

    public static function whois_numeric(BbMes $mes)
    {
        $bb = $mes->bb;
        $paper = $bb->paper;
        if ($mes->user_id == $paper->owner) {
            return 1; // 投稿者
        } else if ($mes->user_id == 0) {
            return 0; // システム
        } else if ($paper->isCoAuthorEmail(User::find($mes->user_id)->email ?? "")) {
            return 2; // 共著者
        } else {
            return 4; // 委員
        }
    }
}
