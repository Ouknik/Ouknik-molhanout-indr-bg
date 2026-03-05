<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Priority: header > user preference > app default
        $locale = $request->header('Accept-Language')
            ?? $request->user()?->locale
            ?? config('app.locale', 'fr');

        $supported = ['ar', 'fr', 'en'];
        $locale = in_array($locale, $supported) ? $locale : 'fr';

        app()->setLocale($locale);

        return $next($request);
    }
}
