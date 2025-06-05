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

    public function enquete()
    {
        return $this->belongsTo(Enquete::class, 'enquete_id');
    }

    /**
     * 明日、受付開始するアンケートを取得する
     * ただし、01-01開始のアンケートは除外する
     */
    public static function tomorrowOpenEnquetes($debugmonth = null, $debugday = null)
    {
        $month = $debugmonth ?? date('n');
        $day = $debugday ?? date('j');
        $tomorrow = Enquete::getTomorrowMonthDay($month, $day);

        return EnqueteConfig::with('enquete')->where('openstart', $tomorrow)
            ->where('valid', true)
            ->whereNotIn('openstart', ['01-01']) // 01-01開始のアンケートは除外
            ->get();
    }
    /**
     * 今日が最終日のアンケートを取得する
     * ただし、12-31終了のアンケートは除外する
     */
    public static function todayCloseEnquetes($debugmonth = null, $debugday = null)
    {
        $month = $debugmonth ?? date('n');
        $day = $debugday ?? date('j');
        $today = sprintf('%02d-%02d', $month, $day);

        return EnqueteConfig::with('enquete')->where('openend', $today)
            ->where('valid', true)
            ->whereNotIn('openend', ['12-31']) // 12-31終了のアンケートは除外
            ->get();
    }

    public function toString()
    {
        return sprintf(
            "EnqueteConfig: %s, %s 〜 %s, %s, %s, %s",
            $this->enquete->name,
            $this->openstart,
            $this->openend,
            $this->catcsv,
            $this->valid ? '有効' : '無効',
            $this->memo,
        );
    }
}
