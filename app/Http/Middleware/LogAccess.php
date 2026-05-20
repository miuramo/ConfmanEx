<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\LogAccess as ModelsLogAccess; // ミドルウェアのLogAccessとかぶるので、別名

class LogAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hozon = $next($request);

        $rooturl = $request->root();
        $uid = (Auth::id() != null) ? Auth::id() : -1;

        // パスワードが生で保存されるのを避ける
        $allreq = $request->all();
        // unset($allreq['password']);
        $hidden = ['password', 'current_password', 'password_confirmation'];
        foreach ($hidden as $h) {
            if (isset($allreq[$h])) $allreq[$h] = '(hidden)';
        }

        // より堅牢なUTF-8クリーニング処理
        array_walk_recursive($allreq, function (&$value) {
            if (is_string($value)) {
                // 不正なUTF-8文字を除去/置換
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');

                // null バイトを除去
                $value = str_replace("\0", '', $value ?? '');

                // 制御文字を除去（改行とタブは保持）
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
            }
        });

        $url = substr($request->fullUrl(), strlen($rooturl));
        if ($url == '/file_favicon' || $url == '/livewire/update' || strlen($url) == 0) return $hozon; // faviconのアクセスはログに残さない

        // URLを取得
        $basePath = '/' . ltrim($request->path(), '/');
        $queryParams = $request->query();
        unset($queryParams['url']); // 不要なパラメータを除外
        if (count($queryParams) > 0) {
            $url = $basePath . '?' . http_build_query($queryParams);
        } else {
            $url = $basePath;
        }
        try {
            $accessLog = new ModelsLogAccess([
                'uid' => $uid,
                'url' => $url,
                'method' => $request->method(),
                'request' => $allreq, //'-',// $request->headers->all(),
            ]);
            $accessLog->save();
        } catch (\Exception $e) {
            info("LogAccess Middleware Error: " . $e->getMessage());
            info($allreq);
        }

        if ($uid > 0 && $url == '/login' && $request->method() == 'POST') {
            // ユーザーログイン時の日時を更新
            $user = Auth::user();
            $user->timestamps = false;
            $user->last_login_at = now();
            $user->save();
            $user->timestamps = true;
        }

        return $hozon;
    }
}
