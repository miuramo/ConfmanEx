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

    public static function update_last_login(int $limit = 10)
    {
        // まだlast_login_atが設定されていないユーザを$limits件取得する
        $users = User::whereNull('last_login_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->pluck('id')->toArray();
            
        info($users);

        foreach ($users as $uid) {
            // 最後のログイン日時を取得
            $lastLogin = LogAccess::where('uid', $uid)
                ->where('url', '/login')
                ->where('method', 'POST')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastLogin) {
                // ユーザのlast_login_atを更新
                $u = User::where('id', $uid)->first();
                if (!$u) continue; // ユーザが存在しない場合はスキップ
                // タイムスタンプを一時的に無効化して更新
                // これにより、last_login_atの更新時にupdated_atが変更されないようにする
                $u->timestamps = false;
                $u->last_login_at = $lastLogin->created_at;
                $u->save();
                $u->timestamps = true;
            }
        }
        return User::whereNull('last_login_at')->count();
    }
}
