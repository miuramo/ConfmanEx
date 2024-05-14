<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (env('APP_ENV') != 'testing') {
            $domain = $request->getHost();

            Config::set('app.url', $request->getScheme() . '://' . $domain);
        }
        // ドメインに基づいてデータベースを切り替えるロジックを実装
        // if ($domain == 'example.com') {
        //     Config::set('database.default', 'mysql');
        // } else if ($domain == 'example2.com') {
        //     Config::set('database.default', 'other_database');
        // }

        return $next($request);
    }
}
