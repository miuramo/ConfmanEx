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
    public function handle(Request $request, Closure $next) : Response
    {
        $hozon = $next($request);

        $rooturl = $request->root();
        $uid = (Auth::id() != null) ? Auth::id() : -1;

        // パスワードが生で保存されるのを避ける
        $allreq = $request->all();
        // unset($allreq['password']);
        $hidden = ['password', 'current_password', 'password_confirmation'];
        foreach($hidden as $h){
            if (isset($allreq[$h])) $allreq[$h] = '(hidden)';
        }
        $accessLog = new ModelsLogAccess([
            'uid' => $uid,
            'url' => substr($request->fullUrl(), strlen($rooturl)),
            'method' => $request->method(),
            'request' => $allreq,//'-',// $request->headers->all(),
        ]);
        $accessLog->save();

        return $hozon;
    }
}
