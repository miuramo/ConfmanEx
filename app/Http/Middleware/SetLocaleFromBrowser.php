<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromBrowser
{
    /**
     * Set the application locale from the browser's preferred supported language.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->getPreferredLanguage(['ja', 'en']) ?? config('app.locale');
        app()->setLocale($locale);

        return $next($request);
    }
}
