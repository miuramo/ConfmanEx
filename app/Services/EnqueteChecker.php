<?php

namespace App\Services;
use App\Models\Enquete;
use App\Models\EnqueteConfig;
use App\Models\EnqueteItem;
use App\Models\Paper;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * EnqueteChecker クラス
 * 
 * アンケートのチェックを行うサービスクラス
 */
class EnqueteChecker
{
    /**
     * 明日、受付開始するアンケートと、今日が最終日のアンケートを取得する
     * ただし、01-01開始と、12-31終了のアンケートは除外する
     */
    public static function tomorrowOpenAndTodayCloseEnqueteConfigs(): Collection
    {
        $tomorrowOpenEnquetes = EnqueteConfig::tomorrowOpenEnquetes();
        $todayCloseEnquetes = EnqueteConfig::todayCloseEnquetes();

        // 明日受付開始のアンケートと今日最終日のアンケートをマージ
        $enqueteConfigs = $tomorrowOpenEnquetes->merge($todayCloseEnquetes);

        // 重複を排除
        return $enqueteConfigs->unique('id');
    }
}