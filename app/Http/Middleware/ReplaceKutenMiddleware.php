<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReplaceKutenMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // リクエストを処理してレスポンスを取得
        $response = $next($request);

        if ($response instanceof BinaryFileResponse) return $response;
        // レスポンスが文字列でない場合はそのまま返す
        if (method_exists($response, 'content') === false || !is_string($response->content())) {
            return $response;
        }

        // 無視するルートを定義
        $excludedRoutes = [
            route('paper.dragontext', ['paper' => 'NUM']),
            route('admin.paperlist'),
            route('review.result', ['cat' => 'NUM']),
            route('review.commentpaper', ['cat' => 'NUM', 'paper' => 'NUM', 'token' => 'HEX']),
            route('admin.crud'),
        ];

        $baseurl = url('/');
        $currenturl = str_replace($baseurl, "", url()->current());
        $currenturl = preg_replace('/\b\d+\b/', 'NUM', $currenturl);
        $currenturl = preg_replace('/\b[0-9a-f]{6,}\b/', 'HEX', $currenturl);
        $currenturl = preg_replace('/\?\w/', '', $currenturl);
        foreach ($excludedRoutes as $url) {
            $url = str_replace($baseurl, "", $url);
            // info("url ".$url);
            // info("CUR ".$currenturl);
        if ($url === $currenturl) {
                return $response;
            }
        }
        // レスポンスがビューの場合の処理
        if ($response instanceof \Illuminate\Http\Response) {
            $content = $response->getContent();

            $replacesetting = Setting::findByIdOrName("REPLACE_PUNCTUATION");
            if ($replacesetting != null && $replacesetting->valid) {
                $replaceary = json_decode($replacesetting->value);
                foreach ($replaceary as $old => $new) {
                    $content = str_replace($old, $new, $content);
                    // $content = str_replace('検索文字列', '置換後の文字列', $content);
                }
            }
            $response->setContent($content);
        }
        // 置換後の内容をレスポンスに設定
        // $response->setContent($content);

        return $response;
    }
}
