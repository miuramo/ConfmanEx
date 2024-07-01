<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        // レスポンスが文字列でない場合はそのまま返す
        if (!is_string($response->content())) {
            return $response;
        }
        
        // レスポンスがビューの場合の処理
        if ($response instanceof \Illuminate\Http\Response) {
            $content = $response->getContent();
            $content = str_replace('。', '．', $content);
            $content = str_replace('、', '，', $content);
                // $content = str_replace('検索文字列', '置換後の文字列', $content);
            $response->setContent($content);
        }
        // 置換後の内容をレスポンスに設定
        // $response->setContent($content);

        return $response;
    }
}
